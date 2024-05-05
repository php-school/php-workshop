<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use GuzzleHttp\Psr7\Message;
use PhpSchool\PhpWorkshop\Check\CodeExistsCheck;
use PhpSchool\PhpWorkshop\Check\CodeParseCheck;
use PhpSchool\PhpWorkshop\Check\FileExistsCheck;
use PhpSchool\PhpWorkshop\Check\PhpLintCheck;
use PhpSchool\PhpWorkshop\Event\CgiExecuteEvent;
use PhpSchool\PhpWorkshop\Event\CgiExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\CgiExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Environment\CgiTestEnvironment;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Process\ProcessFactory;
use PhpSchool\PhpWorkshop\Result\Cgi\CgiResult;
use PhpSchool\PhpWorkshop\Result\Cgi\RequestFailure;
use PhpSchool\PhpWorkshop\Result\Cgi\GenericFailure;
use PhpSchool\PhpWorkshop\Result\Cgi\Success;
use PhpSchool\PhpWorkshop\Result\Cgi\ResultInterface as CgiResultInterface;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Utils\Path;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
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
    private CgiExercise $exercise;

    private EventDispatcher $eventDispatcher;
    private ProcessFactory $processFactory;

    /**
     * @var array<class-string>
     */
    private static array $requiredChecks = [
        FileExistsCheck::class,
        CodeExistsCheck::class,
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
     */
    public function __construct(
        CgiExercise $exercise,
        EventDispatcher $eventDispatcher,
        ProcessFactory $processFactory
    ) {
        /** @var CgiExercise&ExerciseInterface $exercise */
        $this->eventDispatcher = $eventDispatcher;
        $this->exercise = $exercise;
        $this->processFactory = $processFactory;
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
        return self::$requiredChecks;
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
     * @param ExecutionContext $context The runner context.
     * @return CgiResult The result of the check.
     */
    public function verify(ExecutionContext $context): ResultInterface
    {
        $environment = $this->exercise->defineTestEnvironment();
        $this->setupStudentEnvironment($context, $environment);

        $this->eventDispatcher->dispatch(new CgiExerciseRunnerEvent('cgi.verify.start', $context, $environment));
        $result = new CgiResult(
            array_map(
                function (RequestInterface $request) use ($context, $environment) {
                    return $this->doVerify($context, $environment, $request);
                },
                $environment->executions
            )
        );

        $this->cleanupStudentEnvironment($context, $environment);

        $this->eventDispatcher->dispatch(new CgiExerciseRunnerEvent('cgi.verify.finish', $context, $environment));
        return $result;
    }

    private function doVerify(
        ExecutionContext $context,
        CgiTestEnvironment $environment,
        RequestInterface $request
    ): CgiResultInterface {
        try {
            /** @var CgiExecuteEvent $event */
            $event = $this->eventDispatcher->dispatch(
                new CgiExecuteEvent('cgi.verify.reference-execute.pre', $context, $environment, $request)
            );
            $solutionResponse = $this->executePhpFile(
                $environment,
                $context->referenceExecutionDirectory,
                $context,
                $this->exercise->getSolution()->getEntryPoint()->getAbsolutePath(),
                $event->getRequest(),
                'reference'
            );
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(
                new CgiExecuteEvent(
                    'cgi.verify.reference-execute.fail',
                    $context,
                    $environment,
                    $request,
                    ['exception' => $e]
                )
            );
            throw new SolutionExecutionException($e->getMessage());
        }

        $this->setupStudentEnvironment($context, $environment);
        try {
            /** @var CgiExecuteEvent $event */
            $event = $this->eventDispatcher->dispatch(
                new CgiExecuteEvent('cgi.verify.student-execute.pre', $context, $environment, $request)
            );
            $userResponse = $this->executePhpFile(
                $environment,
                $context->studentExecutionDirectory,
                $context,
                basename($context->getEntryPoint()),
                $event->getRequest(),
                'student'
            );
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(
                new CgiExecuteEvent(
                    'cgi.verify.student-execute.fail',
                    $context,
                    $environment,
                    $request,
                    ['exception' => $e]
                )
            );
            return GenericFailure::fromRequestAndCodeExecutionFailure($request, $e);
        } finally {
            $this->cleanupStudentEnvironment($context, $environment);
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

    private function executePhpFile(
        CgiTestEnvironment $environment,
        string $workingDirectory,
        ExecutionContext $context,
        string $fileName,
        RequestInterface $request,
        string $type
    ): ResponseInterface {
        $process = $this->getProcess($workingDirectory, basename($fileName), $request);
        $process->start();
        $this->eventDispatcher->dispatch(
            new CgiExecuteEvent(sprintf('cgi.verify.%s.executing', $type), $context, $environment, $request)
        );
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
    private function getProcess(string $workingDirectory, string $fileName, RequestInterface $request): Process
    {
        $env = [
            'REQUEST_METHOD'  => $request->getMethod(),
            //'SCRIPT_FILENAME' => '/solution/' . basename($fileName), // TODO: Figure out this path in the container
            'SCRIPT_FILENAME' => $fileName,
            'REDIRECT_STATUS' => '302',
            'QUERY_STRING'    => $request->getUri()->getQuery(),
            'REQUEST_URI'     => $request->getUri()->getPath(),
            'XDEBUG_MODE'     => 'off',
        ];

        $content                = $request->getBody()->__toString();
        $env['CONTENT_LENGTH']  = (string) $request->getBody()->getSize();
        $env['CONTENT_TYPE']    = $request->getHeaderLine('Content-Type');

        foreach ($request->getHeaders() as $name => $values) {
            $env[sprintf('HTTP_%s', strtoupper($name))] = implode(", ", $values);
        }

        return $this->processFactory->create(
            'php-cgi',
            [
                '-dalways_populate_raw_post_data=-1',
                '-dhtml_errors=0',
                '-dexpose_php=0',
            ],
            $workingDirectory,
            $env,
            $content
        );
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
     * @param ExecutionContext $context
     * @param OutputInterface $output A wrapper around STDOUT.
     * @return bool If the solution was successfully executed, eg. exit code was 0.
     */
    public function run(ExecutionContext $context, OutputInterface $output): bool
    {
        $environment = $this->exercise->defineTestEnvironment();
        $this->setupStudentEnvironment($context, $environment);

        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('cgi.run.start', $context));
        $success = true;
        foreach ($environment->executions as $i => $request) {
            /** @var CgiExecuteEvent $event */
            $event = $this->eventDispatcher->dispatch(
                new CgiExecuteEvent('cgi.run.student-execute.pre', $context, $environment, $request)
            );
            $process = $this->getProcess(
                $context->studentExecutionDirectory,
                basename($context->getEntryPoint()),
                $event->getRequest()
            );

            $process->start();
            $this->eventDispatcher->dispatch(
                new CgiExecuteEvent(
                    'cgi.run.student.executing',
                    $context,
                    $environment,
                    $request,
                    ['output' => $output]
                )
            );
            $process->wait(function ($outputType, $outputBuffer) use ($output) {
                $output->write($outputBuffer);
            });
            $output->emptyLine();

            if (!$process->isSuccessful()) {
                $success = false;
            }

            $output->lineBreak();

            $this->eventDispatcher->dispatch(
                new CgiExecuteEvent('cgi.run.student-execute.post', $context, $environment, $request)
            );
        }

        $this->cleanupStudentEnvironment($context, $environment);

        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('cgi.run.finish', $context));
        return $success;
    }

    private function setupStudentEnvironment(
        ExecutionContext $context,
        CgiTestEnvironment $environment
    ): void {
        $filesystem = new Filesystem();

        foreach ($environment->files as $fileName => $content) {
            $filesystem->dumpFile(Path::join($context->studentExecutionDirectory, $fileName), $content);
        }
    }

    private function cleanupStudentEnvironment(
        ExecutionContext $context,
        CgiTestEnvironment $environment
    ): void {
        $filesystem = new Filesystem();

        foreach ($environment->files as $fileName => $content) {
            $filesystem->remove(Path::join($context->studentExecutionDirectory, $fileName));
        }
    }
}
