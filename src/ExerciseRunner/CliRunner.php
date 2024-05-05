<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use PhpSchool\PhpWorkshop\Check\CodeExistsCheck;
use PhpSchool\PhpWorkshop\Check\CodeParseCheck;
use PhpSchool\PhpWorkshop\Check\FileExistsCheck;
use PhpSchool\PhpWorkshop\Check\PhpLintCheck;
use PhpSchool\PhpWorkshop\Event\CliExecuteEvent;
use PhpSchool\PhpWorkshop\Event\CliExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\CliExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\Environment;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Environment\CliTestEnvironment;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Process\ProcessFactory;
use PhpSchool\PhpWorkshop\Result\Cli\RequestFailure;
use PhpSchool\PhpWorkshop\Result\Cli\CliResult;
use PhpSchool\PhpWorkshop\Result\Cli\GenericFailure;
use PhpSchool\PhpWorkshop\Result\Cli\Success;
use PhpSchool\PhpWorkshop\Result\Cli\ResultInterface as CliResultInterface;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PhpSchool\PhpWorkshop\Utils\Collection;
use PhpSchool\PhpWorkshop\Utils\System;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use PhpSchool\PhpWorkshop\Utils\Path;

/**
 * The `CLI` runner. This runner executes solutions as PHP CLI scripts, passing the arguments
 * from the exercise as command line arguments to the solution. The solution will be invoked like:
 *
 * ```bash
 * php my-solution.php arg1 arg2 arg3
 * ```
 */
class CliRunner implements ExerciseRunnerInterface
{
    /**
     * @var CliExercise&ExerciseInterface
     */
    private $exercise;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    private ProcessFactory $processFactory;

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
     * Requires the exercise instance and an event dispatcher.
     *
     * @param CliExercise $exercise The exercise to be invoked.
     * @param EventDispatcher $eventDispatcher The event dispatcher.
     */
    public function __construct(CliExercise $exercise, EventDispatcher $eventDispatcher, ProcessFactory $processFactory)
    {
        /** @var CliExercise&ExerciseInterface $exercise */
        $this->eventDispatcher = $eventDispatcher;
        $this->exercise = $exercise;
        $this->processFactory = $processFactory;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'CLI Program Runner';
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
     * Verifies a solution by invoking PHP from the CLI passing the arguments gathered from the exercise
     * as command line arguments to PHP.
     *
     * Events dispatched:
     *
     * * cli.verify.reference-execute.pre
     * * cli.verify.reference.executing
     * * cli.verify.reference-execute.fail (if the reference solution fails to execute)
     * * cli.verify.student-execute.pre
     * * cli.verify.student.executing
     * * cli.verify.student-execute.fail (if the student's solution fails to execute)
     *
     * @param ExecutionContext $context
     * @return CliResult The result of the check.
     */
    public function verify(ExecutionContext $context): ResultInterface
    {
        $environment = $this->exercise->defineTestEnvironment();
        $this->setupStudentEnvironment($context, $environment);

        $this->eventDispatcher->dispatch(new CliExerciseRunnerEvent('cli.verify.start', $context, $environment));

        $result = new CliResult(
            array_map(
                function (Collection $args) use ($context, $environment) {
                    return $this->doVerify($context, $environment, $args);
                },
                $environment->executions
            )
        );

        $this->cleanupStudentEnvironment($context, $environment);

        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('cli.verify.finish', $context));
        return $result;
    }

    /**
     * @param Collection<int,string> $args
     */
    private function doVerify(
        ExecutionContext $context,
        CliTestEnvironment $environment,
        Collection $args
    ): CliResultInterface {
        try {
            /** @var CliExecuteEvent $event */
            $event = $this->eventDispatcher->dispatch(
                new CliExecuteEvent('cli.verify.reference-execute.pre', $context, $environment, $args)
            );
            $solutionOutput = $this->executePhpFile(
                $context,
                $environment,
                $context->referenceExecutionDirectory,
                $this->exercise->getSolution()->getEntryPoint()->getRelativePath(),
                $event->getArgs(),
                'reference'
            );
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(
                new CliExecuteEvent(
                    'cli.verify.reference-execute.fail',
                    $context,
                    $environment,
                    $args,
                    ['exception' => $e]
                )
            );
            throw new SolutionExecutionException($e->getMessage());
        }

        try {
            /** @var CliExecuteEvent $event */
            $event = $this->eventDispatcher->dispatch(
                new CliExecuteEvent('cli.verify.student-execute.pre', $context, $environment, $args)
            );
            $userOutput = $this->executePhpFile(
                $context,
                $environment,
                $context->studentExecutionDirectory,
                basename($context->getEntryPoint()),
                $event->getArgs(),
                'student'
            );
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(
                new CliExecuteEvent(
                    'cli.verify.student-execute.fail',
                    $context,
                    $environment,
                    $args,
                    ['exception' => $e]
                )
            );
            return GenericFailure::fromArgsAndCodeExecutionFailure($args, $e);
        }
        if ($solutionOutput === $userOutput) {
            return new Success($event->getArgs());
        }

        return RequestFailure::fromArgsAndOutput($event->getArgs(), $solutionOutput, $userOutput);
    }

    /**
     * @param Collection<int, string> $args
     */
    private function executePhpFile(
        ExecutionContext $context,
        CliTestEnvironment $testEnvironment,
        string $workingDirectory,
        string $fileName,
        Collection $args,
        string $type
    ): string {
        $process = $this->getPhpProcess($workingDirectory, $fileName, $args);

        $process->start();
        $this->eventDispatcher->dispatch(
            new CliExecuteEvent(sprintf('cli.verify.%s.executing', $type), $context, $testEnvironment, $args)
        );
        $process->wait();

        if (!$process->isSuccessful()) {
            throw CodeExecutionException::fromProcess($process);
        }

        return $process->getOutput();
    }

    /**
     * @param string $fileName
     * @param Collection<int, string> $args
     *
     * @return Process
     */
    private function getPhpProcess(string $workingDirectory, string $fileName, Collection $args): Process
    {
        return $this->processFactory->create(
            'php',
            [$fileName, ...$args],
            $workingDirectory,
            [],
        );
    }

    /**
     * Runs a student's solution by invoking PHP from the CLI passing the arguments gathered from the exercise
     * as command line arguments to PHP.
     *
     * Running only runs the student's solution, the reference solution is not run and no verification is performed,
     * the output of the student's solution is written directly to the output.
     *
     * Events dispatched:
     *
     *  * cli.run.student-execute.pre
     *  * cli.run.student.executing
     *
     * @param ExecutionContext $context
     * @param OutputInterface $output A wrapper around STDOUT.
     * @return bool If the solution was successfully executed, eg. exit code was 0.
     */
    public function run(ExecutionContext $context, OutputInterface $output): bool
    {
        $environment = $this->exercise->defineTestEnvironment();
        $this->setupStudentEnvironment($context, $environment);

        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('cli.run.start', $context));
        $success = true;
        foreach ($environment->executions as $i => $args) {
            /** @var CliExecuteEvent $event */
            $event = $this->eventDispatcher->dispatch(
                new CliExecuteEvent('cli.run.student-execute.pre', $context, $environment, $args)
            );

            $args = $event->getArgs();

            $process = $this->getPhpProcess(
                $context->studentExecutionDirectory,
                basename($context->getEntryPoint()),
                $args
            );
            $process->start();
            $this->eventDispatcher->dispatch(
                new CliExecuteEvent('cli.run.student.executing', $context, $environment, $args, ['output' => $output])
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
                new CliExecuteEvent('cli.run.student-execute.post', $context, $environment, $args)
            );
        }

        $this->cleanupStudentEnvironment($context, $environment);

        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('cli.run.finish', $context));
        return $success;
    }

    private function setupStudentEnvironment(
        ExecutionContext $context,
        CliTestEnvironment $environment
    ): void {
        $filesystem = new Filesystem();

        foreach ($environment->files as $fileName => $content) {
            $filesystem->dumpFile(Path::join($context->studentExecutionDirectory, $fileName), $content);
        }
    }

    private function cleanupStudentEnvironment(
        ExecutionContext $context,
        CliTestEnvironment $environment
    ): void {
        $filesystem = new Filesystem();

        foreach ($environment->files as $fileName => $content) {
            $filesystem->remove(Path::join($context->studentExecutionDirectory, $fileName));
        }
    }
}
