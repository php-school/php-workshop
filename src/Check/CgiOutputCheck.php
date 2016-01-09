<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\CgiOutputExerciseCheck;
use PhpSchool\PhpWorkshop\ProcessExecutor\CgiProcessExecutor;
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
            $solutionResponse = $this->executePhpFile($exercise->getSolution()->getEntryPoint(), $request);
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
        $executor = new CgiProcessExecutor($request);

        return $executor->asResponseObject($fileName);
    }
}
