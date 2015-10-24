<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\CgiOutputExerciseCheck;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;
use PhpSchool\PhpWorkshop\Result\Success;
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

        $method = $exercise->getMethod();
        $args   = $exercise->getArgs();

        try {
            $solutionOutput = $this->parseHttpResponseBody(
                $this->executePhpFile($exercise->getSolution(), $args, $method)
            );
        } catch (RuntimeException $e) {
            throw new SolutionExecutionException($e->getMessage());
        }

        try {
            $userOutput = $this->parseHttpResponseBody(
                $this->executePhpFile($fileName, $args, $method)
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
     * @param array $args
     * @param string $method
     * @return string
     */
    private function executePhpFile($fileName, array $args, $method)
    {
        $env = [
            'REQUEST_METHOD'  => $method,
            'SCRIPT_FILENAME' => $fileName,
            'REDIRECT_STATUS' => 302,
            'QUERY_STRING'    => http_build_query($args),
        ];
        
        $cgi = sprintf('php-cgi%s', DIRECTORY_SEPARATOR === '\\' ? '.exe' : '');
        $cgiBinary  = sprintf(
            '%s -dalways_populate_raw_post_data=-1',
            realpath(sprintf('%s/%s', str_replace('\\', '/', dirname(PHP_BINARY)), $cgi))
        );
        switch ($method) {
            case CgiOutputExerciseCheck::METHOD_POST:
                $content                = http_build_query($args);
                $env['CONTENT_LENGTH']  = mb_strlen($content);
                $env['CONTENT_TYPE']    = 'application/x-www-form-urlencoded';
                $cmd                    = sprintf('echo %s | %s', $content, $cgiBinary);
                break;
            case CgiOutputExerciseCheck::METHOD_GET:
            default:
                $cmd = sprintf('echo "" | %s', $cgiBinary);
                break;
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
