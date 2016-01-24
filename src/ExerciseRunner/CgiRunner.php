<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use PhpSchool\PhpWorkshop\Event\CgiExecuteEvent;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\CgiExercise;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
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
 * Class CgiRunner
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiRunner implements ExerciseRunnerInterface
{

    /**
     * @var CgiExercise
     */
    private $exercise;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param CgiExercise $exercise
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(CgiExercise $exercise, EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->exercise = $exercise;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'CGI Program Runner';
    }

    /**
     * @param RequestInterface $request
     * @param string $fileName
     * @return ResultInterface
     */
    private function checkRequest(RequestInterface $request, $fileName)
    {
        try {
            $event = $this->eventDispatcher->dispatch(new CgiExecuteEvent('cgi.verify.solution-execute.pre', $request));
            $solutionResponse = $this->executePhpFile(
                $this->exercise->getSolution()->getEntryPoint(),
                $event->getRequest(),
                'solution'
            );
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(new Event('cgi.verify.solution-execute.fail', ['exception' => $e]));
            throw new SolutionExecutionException($e->getMessage());
        }

        try {
            $event = $this->eventDispatcher->dispatch(new CgiExecuteEvent('cgi.verify.user-execute.pre', $request));
            $userResponse = $this->executePhpFile($fileName, $event->getRequest(), 'user');
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(new Event('cgi.verify.user-execute.fail', ['exception' => $e]));
            return Failure::fromNameAndCodeExecutionFailure($this->getName(), $e);
        }
        
        $solutionBody       = (string) $solutionResponse->getBody();
        $userBody           = (string) $userResponse->getBody();
        $solutionHeaders    = $this->getHeaders($solutionResponse);
        $userHeaders        = $this->getHeaders($userResponse);

        if ($solutionBody !== $userBody || $solutionHeaders !== $userHeaders) {
            return new CgiOutRequestFailure($request, $solutionBody, $userBody, $solutionHeaders, $userHeaders);
        }

        return new Success($this->getName());
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
     * @param string $type
     * @return ResponseInterface
     */
    private function executePhpFile($fileName, RequestInterface $request, $type)
    {
        $process = $this->getProcess($fileName, $request);

        $process->start();
        $this->eventDispatcher->dispatch(new CgiExecuteEvent(sprintf('cgi.verify.%s.executing', $type), $request));
        $process->wait();

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
     * @param string $fileName
     * @param RequestInterface $request
     * @return Process
     */
    private function getProcess($fileName, RequestInterface $request)
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

        return new Process($cmd, null, $env);
    }

    /**
     * @param string $fileName
     * @return ResultInterface
     */
    public function verify($fileName)
    {
        return new CgiOutResult(
            $this->getName(),
            array_map(
                function (RequestInterface $request) use ($fileName) {
                    return $this->checkRequest($request, $fileName);
                },
                $this->exercise->getRequests()
            )
        );
    }

    /**
     * @param string $fileName
     * @param OutputInterface $output
     * @return bool
     */
    public function run($fileName, OutputInterface $output)
    {
        $success = true;
        foreach ($this->exercise->getRequests() as $i => $request) {
            $event      = $this->eventDispatcher->dispatch(new CgiExecuteEvent('cgi.run.usr-execute.pre', $request));
            $process    = $this->getProcess($fileName, $event->getRequest());

            $process->run(function ($outputType, $outputBuffer) use ($output) {
                $output->write($outputBuffer);
            });

            if (!$process->isSuccessful()) {
                $success = false;
            }
        }
        return $success;
    }
}
