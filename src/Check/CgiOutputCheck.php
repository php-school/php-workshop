<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\CgiOutputExerciseCheck;
use PhpSchool\PhpWorkshop\Result\CgiOutBodyFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutHeadersFailure;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Symfony\Component\Process\Process;
use Zend\Diactoros\Response\Serializer as ResponseSerializer;

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
            $solutionResponse = $this->executePhpFile($exercise->getSolution(), $request);
        } catch (RuntimeException $e) {
            throw new SolutionExecutionException($e->getMessage());
        }

        try {
            $userResponse = $this->executePhpFile($fileName, $request);
        } catch (RuntimeException $e) {
            return new Failure('Program Output', sprintf('PHP Code failed to execute. Error: "%s"', $e->getMessage()));
        }

        //compare body
        if ($solutionResponse->getBody()->__toString() !== $userResponse->getBody()->__toString()) {
            return new CgiOutBodyFailure(
                $solutionResponse->getBody()->__toString(),
                $userResponse->getBody()->__toString()
            );
        }
        
        //compare headers
        $solutionHeaders = [];
        foreach ($solutionResponse->getHeaders() as $name => $values) {
            $solutionHeaders[$name] = implode(", ", $values);
        }
        
        $userHeaders = [];
        foreach ($userResponse->getHeaders() as $name => $values) {
            $userHeaders[$name] = implode(", ", $values);
        }
        
        if ($userHeaders !== $solutionHeaders) {
            return new CgiOutHeadersFailure($solutionHeaders, $userHeaders);
        }
        
        return new Success('Program Output');
    }

    /**
     * @param $fileName
     * @param RequestInterface $request
     * @return ResponseInterface
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
        
        //if no status line, pre-pend 200 OK
        $output = $process->getOutput();
        if (!preg_match('/^HTTP\/([1-9]\d*\.\d) ([1-5]\d{2})(\s+(.+))?\\r\\n/', $output)) {
            $output = "HTTP/1.0 200 OK\r\n" . $output;
        }

        return ResponseSerializer::fromString($output);
    }
    
    /**
     * @return bool
     */
    public function breakChainOnFailure()
    {
        return false;
    }
}
