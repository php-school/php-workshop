<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use GuzzleHttp\Psr7\Message;
use PhpSchool\PhpWorkshop\Check\CodeParseCheck;
use PhpSchool\PhpWorkshop\Check\FileExistsCheck;
use PhpSchool\PhpWorkshop\Check\PhpLintCheck;
use PhpSchool\PhpWorkshop\Event\CgiExecuteEvent;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\CgiExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Result\Cgi\CgiResult;
use PhpSchool\PhpWorkshop\Result\Cgi\RequestFailure;
use PhpSchool\PhpWorkshop\Result\Cgi\GenericFailure;
use PhpSchool\PhpWorkshop\Result\Cgi\Success;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Utils\RequestRenderer;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * The `CGI` runner. This runner executes solutions as if they were behind a web-server. They populate the `$_SERVER`,
 * `$_GET` & `$_POST` super globals with information based of the request objects returned from the exercise.
 */
class CgiRunner implements ExerciseRunnerInterface
{
    /**
     * @var CgiExercise&ExerciseInterface
     */
    private $exercise;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var RequestRenderer
     */
    private $requestRenderer;

    /**
     * @var array<class-string>
     */
    private static $requiredChecks = [
        FileExistsCheck::class,
        PhpLintCheck::class,
        CodeParseCheck::class,
    ];

    /**
     * Requires the exercise instance and an event dispatcher. This runner requires the `php-cgi` binary to
     * be available. It will check for it's existence in the system's $PATH variable or the same
     * folder that the CLI php binary lives in.
     *
     * @param CgiExercise $exercise The exercise to be invoked.
     * @param EventDispatcher $eventDispatcher The event dispatcher.
     * @param RequestRenderer $requestRenderer
     */
    public function __construct(
        CgiExercise $exercise,
        EventDispatcher $eventDispatcher,
        RequestRenderer $requestRenderer
    ) {
        if (PHP_OS_FAMILY === 'Windows') {
            // Check if in path. 2> nul > nul equivalent to 2>&1 /dev/null
            $silence  = (PHP_OS === 'CYGWIN' ? '> /dev/null 2>&1' : '2> nul > nul');
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
        /** @var CgiExercise&ExerciseInterface $exercise */
        $this->eventDispatcher = $eventDispatcher;
        $this->exercise = $exercise;
        $this->requestRenderer = $requestRenderer;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'CGI Program Runner';
    }

    /**
     * Get an array of the class names of the required checks this runner needs.
     *
     * @return array<class-string>
     */
    public function getRequiredChecks(): array
    {
        return static::$requiredChecks;
    }

    /**
     * @param RequestInterface $request
     * @param string $fileName
     * @return ResultInterface
     */
    private function checkRequest(RequestInterface $request, string $fileName): ResultInterface
    {
        try {
            /** @var CgiExecuteEvent $event */
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
            /** @var CgiExecuteEvent $event */
            $event = $this->eventDispatcher->dispatch(new CgiExecuteEvent('cgi.verify.student-execute.pre', $request));
            $userResponse = $this->executePhpFile($fileName, $event->getRequest(), 'student');
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(new Event('cgi.verify.student-execute.fail', ['exception' => $e]));
            return GenericFailure::fromRequestAndCodeExecutionFailure($request, $e);
        }

        $solutionBody       = (string) $solutionResponse->getBody();
        $userBody           = (string) $userResponse->getBody();
        $solutionHeaders    = $this->getHeaders($solutionResponse);
        $userHeaders        = $this->getHeaders($userResponse);

        if ($solutionBody !== $userBody || $solutionHeaders !== $userHeaders) {
            return new RequestFailure($request, $solutionBody, $userBody, $solutionHeaders, $userHeaders);
        }

        return new Success($request);
    }

    /**
     * @param ResponseInterface $response
     * @return array<string,string>
     */
    private function getHeaders(ResponseInterface $response): array
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
    private function executePhpFile(string $fileName, RequestInterface $request, string $type): ResponseInterface
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

        return Message::parseResponse($output);
    }

    /**
     * @param string $fileName
     * @param RequestInterface $request
     * @return Process
     */
    private function getProcess(string $fileName, RequestInterface $request): Process
    {
        $env = [
            'REQUEST_METHOD'  => $request->getMethod(),
            'SCRIPT_FILENAME' => $fileName,
            'REDIRECT_STATUS' => 302,
            'QUERY_STRING'    => $request->getUri()->getQuery(),
            'REQUEST_URI'     => $request->getUri()->getPath()
        ];

        $cgi = sprintf('php-cgi%s', DIRECTORY_SEPARATOR === '\\' ? '.exe' : '');
        $cgiBinary = sprintf(
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

        return Process::fromShellCommandline($cmd, null, $env, null, 10);
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
     * @return CgiResult The result of the check.
     */
    public function verify(Input $input): ResultInterface
    {
        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('cgi.verify.start', $this->exercise, $input));
        $result = new CgiResult(
            array_map(
                function (RequestInterface $request) use ($input) {
                    return $this->checkRequest($request, $input->getRequiredArgument('program'));
                },
                $this->exercise->getRequests()
            )
        );
        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('cgi.verify.finish', $this->exercise, $input));
        return $result;
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
    public function run(Input $input, OutputInterface $output): bool
    {
        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('cgi.run.start', $this->exercise, $input));
        $success = true;
        foreach ($this->exercise->getRequests() as $i => $request) {
            /** @var CgiExecuteEvent $event */
            $event = $this->eventDispatcher->dispatch(
                new CgiExecuteEvent('cgi.run.student-execute.pre', $request)
            );
            $process = $this->getProcess($input->getRequiredArgument('program'), $event->getRequest());

            $output->writeTitle("Request");
            $output->emptyLine();
            $output->write($this->requestRenderer->renderRequest($request));

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
        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('cgi.run.finish', $this->exercise, $input));
        return $success;
    }
}
