<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\CgiOutputExerciseCheck;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;
use PhpSchool\PhpWorkshop\Result\Success;
use Psr\Http\Message\RequestInterface;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Class CgiOutputCheck
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiOutputCheck implements CheckInterface
{

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return ResultInterface
     */
    public function check(ExerciseInterface $exercise, $fileName)
    {
        if (!$exercise instanceof CgiOutputExerciseCheck) {
            throw new \InvalidArgumentException;
        }

        $request = $exercise->getRequest();

        try {
            $solutionOutput = $this->parseHttpResponseBody(
                $this->executePhpFile($exercise->getSolution(), $request)
            );
        } catch (RuntimeException $e) {
            throw new SolutionExecutionException($e->getMessage());
        }

        try {
            $userOutput = $this->parseHttpResponseBody(
                $this->executePhpFile($fileName, $request)
            );
        } catch (RuntimeException $e) {
            return new Failure('Program Output', sprintf('PHP Code failed to execute. Error: "%s"', $e->getMessage()));
        }


        if ($solutionOutput === $userOutput) {
            return new Success('Program Output');
        }

        return new StdOutFailure($solutionOutput, $userOutput);
    }

    /**
     * @param $fileName
     * @param RequestInterface $request
     * @return string
     */
    private function executePhpFile($fileName, RequestInterface $request)
    {
        $env = [
            'REQUEST_METHOD'  => $request->getMethod(),
            'SCRIPT_FILENAME' => $fileName,
            'REDIRECT_STATUS' => 302,
            'QUERY_STRING'    => $request->getUri()->getQuery(),
            'REQUEST_URI'     => $request->getUri()->getPath()
        ];
        
        $cgi = sprintf('php-cgi%s', DIRECTORY_SEPARATOR === '\\' ? '.exe' : '');
        $cgiBinary  = sprintf(
            '%s -dalways_populate_raw_post_data=-1 -dhtml_errors=0',
            realpath(sprintf('%s/%s', str_replace('\\', '/', dirname(PHP_BINARY)), $cgi))
        );

        $content                = $request->getBody()->__toString();
        $cmd                    = sprintf('echo %s | %s', $content, $cgiBinary);
        $env['CONTENT_LENGTH']  = $request->getBody()->getSize();
        $env['CONTENT_TYPE']    = $request->getHeaderLine('Content-Type');
        
        foreach ($request->getHeaders() as $name => $values) {
            $env[sprintf('HTTP_%s', strtoupper($name))] = implode(", ", $values);
        }
        
        $process = new Process($cmd, null, $env);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput());
        }

        return $process->getOutput();
    }
    
    /**
     * @return bool
     */
    public function breakChainOnFailure()
    {
        return false;
    }

    /**
     * @param string $response
     * @return string
     */
    private function parseHttpResponseBody($response)
    {
        $content    = '';
        $str        = strtok($response, "\n");
        $h          = null;
        while ($str !== false) {
            if ($h && trim($str) === '') {
                $h = false;
                continue;
            }
            if ($h !== false && false !== strpos($str, ':')) {
                $h = true;
            }
            if ($h === false) {
                $content .= $str . "\n";
            }
            $str = strtok("\n");
        }
        return trim($content);
    }
}
