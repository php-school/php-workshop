<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use GuzzleHttp\Psr7\Message;
use PhpSchool\PhpWorkshop\Check\CodeExistsCheck;
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
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\CgiContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\CliContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\Environment;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\RunnerContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Process\ProcessFactory;
use PhpSchool\PhpWorkshop\Result\Cgi\CgiResult;
use PhpSchool\PhpWorkshop\Result\Cgi\RequestFailure;
use PhpSchool\PhpWorkshop\Result\Cgi\GenericFailure;
use PhpSchool\PhpWorkshop\Result\Cgi\Success;
use PhpSchool\PhpWorkshop\Result\Cgi\ResultInterface as CgiResultInterface;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PhpSchool\PhpWorkshop\Utils\Path;
use PhpSchool\PhpWorkshop\Utils\RequestRenderer;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ExecutableFinder;
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
     * @var ProcessFactory
     */
    private $processFactory;

    /**
     * @var array<class-string>
     */
    private static $requiredChecks = [
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

    private function checkRequest(CgiContext $context, RequestInterface $request): CgiResultInterface
    {
        $this->setupEnvironment($context->getExecutionContext(), $this->exercise->getSolution());

        try {
            /** @var CgiExecuteEvent $event */
            $event = $this->eventDispatcher->dispatch(
                new CgiExecuteEvent('cgi.verify.reference-execute.pre', $context, $request)
            );
            $solutionResponse = $this->executePhpFile(
                $context->getExecutionContext()->referenceEnvironment,
                $context,
                $this->exercise->getSolution()->getEntryPoint()->getAbsolutePath(),
                $event->getRequest(),
                'reference'
            );
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(
                new CgiExecuteEvent('cgi.verify.reference-execute.fail', $context, $request, ['exception' => $e])
            );
            throw new SolutionExecutionException($e->getMessage());
        }

        $this->setupStudentEnvironment($context->getExecutionContext());
        try {
            /** @var CgiExecuteEvent $event */
            $event = $this->eventDispatcher->dispatch(
                new CgiExecuteEvent('cgi.verify.student-execute.pre', $context, $request)
            );
            $userResponse = $this->executePhpFile(
                $context->getExecutionContext()->studentEnvironment,
                $context,
                basename($context->getExecutionContext()->getStudentSolutionFilePath()),
                $event->getRequest(),
                'student'
            );
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(
                new CgiExecuteEvent('cgi.verify.student-execute.fail', $context, $request, ['exception' => $e])
            );
            return GenericFailure::fromRequestAndCodeExecutionFailure($request, $e);
        } finally {
            $this->cleanupStudentEnvironment($context->getExecutionContext());
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

    private function setupEnvironment(ExecutionContext $context, SolutionInterface $solution): void
    {
        $filesystem = new Filesystem();

        foreach ($solution->getFiles() as $file) {
            $filesystem->copy(
                $file->getAbsolutePath(),
                Path::join($context->referenceEnvironment->workingDirectory, $file->getRelativePath())
            )
            ;
        }

        foreach ($context->getFiles() as $fileName => $content) {
            file_put_contents(
                Path::join($context->referenceEnvironment->workingDirectory, $fileName),
                $content
            );
        }
//       sleep(1);
    }

    private function setupStudentEnvironment(ExecutionContext $context): void
    {
        $filesystem = new Filesystem();

        foreach ($context->getFiles() as $fileName => $content) {
            $filesystem->dumpFile(Path::join($context->studentEnvironment->workingDirectory, $fileName), $content);
        }
    }

    private function cleanupStudentEnvironment(ExecutionContext $context): void
    {
        $filesystem = new Filesystem();

        foreach ($context->getFiles() as $fileName => $content) {
            $filesystem->remove(Path::join($context->studentEnvironment->workingDirectory, $fileName));
        }
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
    private function executePhpFile(
        Environment $environment,
        CgiContext $context,
        string $fileName,
        RequestInterface $request,
        string $type
    ): ResponseInterface {
        $process = $this->getProcess($environment, basename($fileName), $request);

        $process->start();
        $this->eventDispatcher->dispatch(
            new CgiExecuteEvent(sprintf('cgi.verify.%s.executing', $type), $context, $request)
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
    private function getProcess(Environment $environment, string $fileName, RequestInterface $request): Process
    {
        $env = $this->getDefaultEnv();
        $env += [
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

        return $this->processFactory->phpCgi($environment, $env, $content);
    }

    /**
     * We need to reset env entirely, because Symfony inherits it. We do that by setting all
     * the current env vars to false
     *
     * @return array<string, false>
     */
    private function getDefaultEnv(): array
    {
        $env = array_map(fn () => false, $_ENV);
        $env + array_map(fn () => false, $_SERVER);

        return $env;
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
     * @param CgiContext $context The runner context.
     * @return CgiResult The result of the check.
     */
    public function verify(RunnerContext $context): ResultInterface
    {
        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('cgi.verify.start', $context));
        $result = new CgiResult(
            array_map(
                function (RequestInterface $request) use ($context) {
                    return $this->checkRequest($context, $request);
                },
                $this->exercise->getRequests()
            )
        );
        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('cgi.verify.finish', $context));
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
     * @param CgiContext $context
     * @param OutputInterface $output A wrapper around STDOUT.
     * @return bool If the solution was successfully executed, eg. exit code was 0.
     */
    public function run(RunnerContext $context, OutputInterface $output): bool
    {
        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('cgi.run.start', $context));
        $success = true;
        foreach ($this->exercise->getRequests() as $i => $request) {
            /** @var CgiExecuteEvent $event */
            $event = $this->eventDispatcher->dispatch(
                new CgiExecuteEvent('cgi.run.student-execute.pre', $context, $request)
            );
            $process = $this->getProcess(
                $context->getExecutionContext()->studentEnvironment,
                basename($context->getExecutionContext()->getStudentSolutionFilePath()),
                $event->getRequest()
            );

            $process->start();
            $this->eventDispatcher->dispatch(
                new CgiExecuteEvent('cgi.run.student.executing', $context, $request, ['output' => $output])
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
                new CgiExecuteEvent('cgi.run.student-execute.post', $context, $request)
            );
        }
        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('cgi.run.finish', $context));
        return $success;
    }
}
