<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use PhpSchool\PhpWorkshop\Check\CodeExistsCheck;
use PhpSchool\PhpWorkshop\Check\CodeParseCheck;
use PhpSchool\PhpWorkshop\Check\FileExistsCheck;
use PhpSchool\PhpWorkshop\Check\PhpLintCheck;
use PhpSchool\PhpWorkshop\Event\CliExecuteEvent;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\CliExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Process\ProcessFactory;
use PhpSchool\PhpWorkshop\Result\Cli\RequestFailure;
use PhpSchool\PhpWorkshop\Result\Cli\CliResult;
use PhpSchool\PhpWorkshop\Result\Cli\GenericFailure;
use PhpSchool\PhpWorkshop\Result\Cli\Success;
use PhpSchool\PhpWorkshop\Result\Cli\ResultInterface as CliResultInterface;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PhpSchool\PhpWorkshop\Utils\Collection;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
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
     * @var CliExercise&ExerciseInterface
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
     * @param string $fileName
     * @param Collection<int, string> $args
     * @param string $type
     * @return string
     */
    private function executePhpFile(string $fileName, Collection $args, string $type): string
    {
        $process = $this->getPhpProcess($fileName, $args);

        $process->start();
        $this->eventDispatcher->dispatch(new CliExecuteEvent(sprintf('cli.verify.%s.executing', $type), $args));
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
    private function getPhpProcess(string $fileName, Collection $args): Process
    {
        return $this->processFactory->phpCli($fileName, $args);
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
     * @param Input $input The command line arguments passed to the command.
     * @return CliResult The result of the check.
     */
    public function verify(Input $input): ResultInterface
    {
        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('cli.verify.start', $this->exercise, $input));
        $result = new CliResult(
            array_map(
                fn(array $args) => $this->doVerify($args, $input),
                $this->exercise->getArgs()
            )
        );
        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('cli.verify.finish', $this->exercise, $input));
        return $result;
    }

    /**
     * @param array<string> $args
     * @param Input $input
     * @return CliResultInterface
     */
    private function doVerify(array $args, Input $input): CliResultInterface
    {
        //arrays are not pass-by-ref
        $args = new Collection($args);
        /** @var Collection<int,string> $args */

        try {
            /** @var CliExecuteEvent $event */
            $event = $this->eventDispatcher->dispatch(new CliExecuteEvent('cli.verify.reference-execute.pre', $args));
            $solutionOutput = $this->executePhpFile(
                $this->exercise->getSolution()->getEntryPoint()->getAbsolutePath(),
                $event->getArgs(),
                'reference'
            );
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(new Event('cli.verify.reference-execute.fail', ['exception' => $e]));
            throw new SolutionExecutionException($e->getMessage());
        }

        try {
            /** @var CliExecuteEvent $event */
            $event = $this->eventDispatcher->dispatch(new CliExecuteEvent('cli.verify.student-execute.pre', $args));
            $userOutput = $this->executePhpFile($input->getRequiredArgument('program'), $event->getArgs(), 'student');
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(new Event('cli.verify.student-execute.fail', ['exception' => $e]));
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
     * @param Input $input The command line arguments passed to the command.
     * @param OutputInterface $output A wrapper around STDOUT.
     * @return bool If the solution was successfully executed, eg. exit code was 0.
     */
    public function run(Input $input, OutputInterface $output): bool
    {
        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('cli.run.start', $this->exercise, $input));
        $success = true;
        foreach ($this->exercise->getArgs() as $i => $args) {
            /** @var Collection<int, string> $argsCollection */
            $argsCollection = new Collection($args);
            /** @var CliExecuteEvent $event */
            $event = $this->eventDispatcher->dispatch(
                new CliExecuteEvent('cli.run.student-execute.pre', $argsCollection)
            );

            $args = $event->getArgs();

            $process = $this->getPhpProcess($input->getRequiredArgument('program'), $args);
            $process->start();
            $this->eventDispatcher->dispatch(
                new CliExecuteEvent('cli.run.student.executing', $args, ['output' => $output])
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
                new CliExecuteEvent('cli.run.student-execute.post', $args)
            );
        }

        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('cli.run.finish', $this->exercise, $input));
        return $success;
    }
}
