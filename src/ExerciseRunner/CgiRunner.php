<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use PhpSchool\PhpWorkshop\Check\CodeParseCheck;
use PhpSchool\PhpWorkshop\Check\FileExistsCheck;
use PhpSchool\PhpWorkshop\Check\PhpLintCheck;
use PhpSchool\PhpWorkshop\Event\CgiExecuteEvent;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\CgiExercise;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Result\CgiOutFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutRequestFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutResult;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Symfony\Component\Process\Process;
use Zend\Diactoros\Response\Serializer as ResponseSerializer;

/**
 * The `CGI` runner. This runner executes solutions as if they were behind a web-server. They populate the `$_SERVER`,
 * `$_GET` & `$_POST` super globals with information based of the request objects returned from the exercise.
 *
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
     * Requires the exercise instance and an event dispatcher. This runner requires the `php-cgi` binary to
     * be available. It will check for it's existence in the system's $PATH variable or the same
     * folder that the CLI php binary lives in.
     *
     * @param CgiExercise $exercise The exercise to be invoked.
     * @param EventDispatcher $eventDispatcher The event dispatcher.
     * @throws RuntimeException If the `php-cgi` binary cannot be found.
     */
    public function __construct(CgiExercise $exercise, EventDispatcher $eventDispatcher)
    {
        if (strpos(PHP_OS, 'WIN') !== false) {
            // Check if in path. 2> nul > nul equivalent to 2>&1 /dev/null
            $silence  = (PHP_OS == 'CYGWIN' ? '> /dev/null 2>&1' : '2> nul > nul');
            system(sprintf('php-cgi --version %s', $silence), $failedToRun);
            if ($failedToRun) {
                $newPath = realpath(sprintf('%s/%s', dirname(PHP_BINARY), 'php-cgi.exe'));
                // Try one more time, relying on being in the php binary's directory (where it should be on Windows)
                system(sprintf('%s --version %s', $newPath, $silence), $stillFailedToRun);
                if ($stillFailedToRun) {
                    throw new RuntimeException(
                        'Could not load php-cgi binary. Please install php-cgi using your package manager.'
                    );
                }
            }
        } else {
            @system('php-cgi --version > /dev/null 2>&1', $failedToRun);
            if ($failedToRun) {
                throw new RuntimeException(
                    'Could not load php-cgi binary. Please install php-cgi using your package manager.'
                );
            }
        }
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
     * Get an array of the class names of the required checks this runner needs.
     *
     * @return array
     */
    public function getRequiredChecks()
    {
        return [
            FileExistsCheck::class,
            PhpLintCheck::class,
            CodeParseCheck::class,
        ];
    }

    /**
     * @param RequestInterface $request
     * @param string $fileName
     * @return ResultInterface
     */
    private function checkRequest(RequestInterface $request, $fileName)
    {
        try {
            $event = $this->eventDispatcher->dispatch(
                new CgiExecuteEvent('cgi.verify.reference-execute.pre', $request)
            );
            $solutionResponse = $this->executePhpFile(
                $this->exercise->getSolution()->getEntryPoint(),
                $event->getRequest(),
                'reference'
            );
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(new Event('cgi.verify.reference-execute.fail', ['exception' => $e]));
            throw new SolutionExecutionException($e->getMessage());
        }

        try {
            $event = $this->eventDispatcher->dispatch(new CgiExecuteEvent('cgi.verify.student-execute.pre', $request));
            $userResponse = $this->executePhpFile($fileName, $event->getRequest(), 'student');
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(new Event('cgi.verify.student-execute.fail', ['exception' => $e]));
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
        $cmd                    = sprintf('echo %s | %s', escapeshellarg($content), $cgiBinary);
        $env['CONTENT_LENGTH']  = $request->getBody()->getSize();
        $env['CONTENT_TYPE']    = $request->getHeaderLine('Content-Type');

        foreach ($request->getHeaders() as $name => $values) {
            $env[sprintf('HTTP_%s', strtoupper($name))] = implode(", ", $values);
        }

        return new Process($cmd, null, $env, null, 10);
    }

    /**
     * Verifies a solution by invoking PHP via the `php-cgi` binary, populating all the super globals with
     * the information from the request objects returned from the exercise. The exercise can return multiple
     * requests so the solution will be invoked for however many requests there are.
     *
     * Events dispatched (for each request):
     *
     * * cgi.verify.reference-execute.pre
     * * cgi.verify.reference.executing
     * * cgi.verify.reference-execute.fail (if the reference solution fails to execute)
     * * cgi.verify.student-execute.pre
     * * cgi.verify.student.executing
     * * cgi.verify.student-execute.fail (if the student's solution fails to execute)
     *
     * @param Input $input The command line arguments passed to the command.
     * @return ResultInterface The result of the check.
     */
    public function verify(Input $input)
    {
        return new CgiOutResult(
            $this->getName(),
            array_map(
                function (RequestInterface $request) use ($input) {
                    return $this->checkRequest($request, $input->getArgument('program'));
                },
                $this->exercise->getRequests()
            )
        );
    }

    /**
     * Runs a student's solution by invoking PHP via the `php-cgi` binary, populating all the super globals with
     * the information from the request objects returned from the exercise. The exercise can return multiple
     * requests so the solution will be invoked for however many requests there are.
     *
     * Running only runs the student's solution, the reference solution is not run and no verification is performed,
     * the output of the student's solution is written directly to the output.
     *
     * Events dispatched (for each request):
     *
     * * cgi.run.student-execute.pre
     * * cgi.run.student.executing
     *
     * @param Input $input The command line arguments passed to the command.
     * @param OutputInterface $output A wrapper around STDOUT.
     * @return bool If the solution was successfully executed, eg. exit code was 0.
     */
    public function run(Input $input, OutputInterface $output)
    {
        $success = true;
        foreach ($this->exercise->getRequests() as $i => $request) {
            $event = $this->eventDispatcher->dispatch(
                new CgiExecuteEvent('cgi.run.student-execute.pre', $request)
            );
            $process = $this->getProcess($input->getArgument('program'), $event->getRequest());

            $output->writeTitle("Request");
            $output->emptyLine();
            $output->writeRequest($request);

            $output->writeTitle("Output");
            $output->emptyLine();
            $process->start();
            $this->eventDispatcher->dispatch(
                new CgiExecuteEvent('cgi.run.student.executing', $request, ['output' => $output])
            );
            $process->wait(function ($outputType, $outputBuffer) use ($output) {
                $output->write($outputBuffer);
            });
            $output->emptyLine();

            if (!$process->isSuccessful()) {
                $success = false;
            }

            $output->lineBreak();
        }
        return $success;
    }
}
