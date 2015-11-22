<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\CgiOutputExerciseCheck;
use PhpSchool\PhpWorkshop\Result\CgiOutFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutRequestFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutResult;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Process\Process;
use Zend\Diactoros\Response\Serializer as ResponseSerializer;

/**
 * Class CgiOutputCheck
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiOutputCheck implements CheckInterface
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'CGI Program Output Check';
    }

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
        
        return new CgiOutResult($this, array_map(function (RequestInterface $request) use ($exercise, $fileName) {
            return $this->checkRequest($exercise, $request, $fileName);
        }, $exercise->getRequests()));
    }

    /**
     * @param ExerciseInterface $exercise
     * @param RequestInterface $request
     * @param string $fileName
     * @return ResultInterface
     */
    private function checkRequest(ExerciseInterface $exercise, RequestInterface $request, $fileName)
    {
        try {
            $solutionResponse = $this->executePhpFile(realpath($exercise->getSolution()), $request);
        } catch (CodeExecutionException $e) {
            throw new SolutionExecutionException($e->getMessage());
        }

        try {
            $userResponse = $this->executePhpFile($fileName, $request);
        } catch (CodeExecutionException $e) {
            return Failure::fromCheckAndCodeExecutionFailure($this, $e);
        }
        
        $solutionBody       = (string) $solutionResponse->getBody();
        $userBody           = (string) $userResponse->getBody();
        $solutionHeaders    = $this->getHeaders($solutionResponse);
        $userHeaders        = $this->getHeaders($userResponse);
        
        if ($solutionBody !== $userBody || $solutionHeaders !== $userHeaders) {
            return new CgiOutRequestFailure($this, $request, $solutionBody, $userBody, $solutionHeaders, $userHeaders);
        }

        return Success::fromCheck($this);
    }

    /**
     * @param ResponseInterface $response
     * @return array
     */
    private function getHeaders(ResponseInterface $response)
    {
        $headers = [];
        foreach ($response->getHeaders() as $name => $values) {
            $headers[$name] = implode(", ", $values);
        }
        return $headers;
    }

    /**
     * @param string $fileName
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
            '%s -dalways_populate_raw_post_data=-1 -dhtml_errors=0 -dexpose_php=0',
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
            throw CodeExecutionException::fromProcess($process);
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
