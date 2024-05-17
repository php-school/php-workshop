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
use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\CliExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\Scenario\CliScenario;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Process\ProcessFactory;
use PhpSchool\PhpWorkshop\Process\ProcessInput;
use PhpSchool\PhpWorkshop\Result\Cli\RequestFailure;
use PhpSchool\PhpWorkshop\Result\Cli\CliResult;
use PhpSchool\PhpWorkshop\Result\Cli\GenericFailure;
use PhpSchool\PhpWorkshop\Result\Cli\Success;
use PhpSchool\PhpWorkshop\Result\Cli\ResultInterface as CliResultInterface;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Utils\Collection;
use Symfony\Component\Process\Process;

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
     * @var array<class-string>
     */
    private static array $requiredChecks = [
        FileExistsCheck::class,
        CodeExistsCheck::class,
        PhpLintCheck::class,
        CodeParseCheck::class,
    ];

    /**
     * Requires the exercise instance and an event dispatcher.
     *
     * @param CliExercise&ExerciseInterface $exercise The exercise to be invoked.
     */
    public function __construct(
        private CliExercise $exercise,
        private EventDispatcher $eventDispatcher,
        private ProcessFactory $processFactory,
        private EnvironmentManager $environmentManager,
    ) {}

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
     * @param ExecutionContext $context The current execution context, containing the exercise, input and working directories.
     * @return CliResult The result of the check.
     */
    public function verify(ExecutionContext $context): ResultInterface
    {
        $scenario = $this->exercise->defineTestScenario();

        $this->environmentManager->prepareStudent($context, $scenario);
        $this->environmentManager->prepareReference($context, $scenario);

        $this->eventDispatcher->dispatch(new CliExerciseRunnerEvent('cli.verify.start', $context, $scenario));

        $result = new CliResult(
            array_map(
                function (Collection $args) use ($context, $scenario) {
                    return $this->doVerify($context, $scenario, $args);
                },
                $scenario->getExecutions(),
            ),
        );

        $this->eventDispatcher->dispatch(new CliExerciseRunnerEvent('cli.verify.finish', $context, $scenario));

        return $result;
    }

    /**
     * @param Collection<int, string> $args
     */
    private function doVerify(ExecutionContext $context, CliScenario $scenario, Collection $args): CliResultInterface
    {
        try {
            /** @var CliExecuteEvent $event */
            $event = $this->eventDispatcher->dispatch(
                new CliExecuteEvent('cli.verify.reference-execute.pre', $context, $scenario, $args),
            );
            $solutionOutput = $this->executePhpFile(
                $context,
                $scenario,
                $context->getReferenceExecutionDirectory(),
                $this->exercise->getSolution()->getEntryPoint()->getRelativePath(),
                $event->getArgs(),
                'reference',
            );
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(
                new CliExecuteEvent(
                    'cli.verify.reference-execute.fail',
                    $context,
                    $scenario,
                    $args,
                    ['exception' => $e],
                ),
            );
            throw new SolutionExecutionException($e->getMessage());
        }

        try {
            /** @var CliExecuteEvent $event */
            $event = $this->eventDispatcher->dispatch(
                new CliExecuteEvent('cli.verify.student-execute.pre', $context, $scenario, $args),
            );
            $userOutput = $this->executePhpFile(
                $context,
                $scenario,
                $context->getStudentExecutionDirectory(),
                $context->getEntryPoint(),
                $event->getArgs(),
                'student',
            );
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(
                new CliExecuteEvent(
                    'cli.verify.student-execute.fail',
                    $context,
                    $scenario,
                    $args,
                    ['exception' => $e],
                ),
            );
            return GenericFailure::fromArgsAndCodeExecutionFailure($args, $e);
        }
        if ($solutionOutput === $userOutput) {
            return new Success($event->getArgs());
        }

        return RequestFailure::fromArgsAndOutput($event->getArgs(), $solutionOutput, $userOutput);
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
     * @param ExecutionContext $context The current execution context, containing the exercise, input and working directories.
     * @param OutputInterface $output A wrapper around STDOUT.
     * @return bool If the solution was successfully executed, eg. exit code was 0.
     */
    public function run(ExecutionContext $context, OutputInterface $output): bool
    {
        $scenario = $this->exercise->defineTestScenario();

        $this->environmentManager->prepareStudent($context, $scenario);

        $this->eventDispatcher->dispatch(new CliExerciseRunnerEvent('cli.run.start', $context, $scenario));

        $success = true;
        foreach ($scenario->getExecutions() as $i => $args) {
            /** @var CliExecuteEvent $event */
            $event = $this->eventDispatcher->dispatch(
                new CliExecuteEvent('cli.run.student-execute.pre', $context, $scenario, $args),
            );

            $args = $event->getArgs();

            $process = $this->getPhpProcess(
                $context->getStudentExecutionDirectory(),
                $context->getEntryPoint(),
                $args,
            );

            $process->start();
            $this->eventDispatcher->dispatch(
                new CliExecuteEvent('cli.run.student.executing', $context, $scenario, $args, ['output' => $output]),
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
                new CliExecuteEvent('cli.run.student-execute.post', $context, $scenario, $args),
            );
        }

        $this->eventDispatcher->dispatch(new CliExerciseRunnerEvent('cli.run.finish', $context, $scenario));

        return $success;
    }

    /**
     * @param Collection<int, string> $args
     */
    private function executePhpFile(ExecutionContext $context, CliScenario $scenario, string $workingDirectory, string $fileName, Collection $args, string $type): string
    {
        $process = $this->getPhpProcess($workingDirectory, $fileName, $args);

        $process->start();
        $this->eventDispatcher->dispatch(
            new CliExecuteEvent(sprintf('cli.verify.%s.executing', $type), $context, $scenario, $args),
        );
        $process->wait();

        if (!$process->isSuccessful()) {
            throw CodeExecutionException::fromProcess($process);
        }

        return $process->getOutput();
    }

    /**
     * @param Collection<int, string> $args
     */
    private function getPhpProcess(string $workingDirectory, string $fileName, Collection $args): Process
    {
        return $this->processFactory->create(
            new ProcessInput('php', [$fileName, ...$args->getArrayCopy()], $workingDirectory, []),
        );
    }
}
