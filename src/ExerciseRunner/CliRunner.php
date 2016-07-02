<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use PhpSchool\PhpWorkshop\Event\CliEvent;
use PhpSchool\PhpWorkshop\Event\CliExecuteEvent;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\CliExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseCheck\StdOutExerciseCheck;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use Symfony\Component\Process\Process;

/**
 * The `CLI` runner. This runner executes solutions as PHP CLI scripts, passing the arguments
 * from the exercise as command line arguments to the solution. The solution will be invoked like:
 *
 * ```bash
 * php my-solution.php arg1 arg2 arg3
 * ```
 *
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CliRunner implements ExerciseRunnerInterface
{
    /**
     * @var CliExercise
     */
    private $exercise;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * Requires the exercise instance and an event dispatcher.
     *
     * @param CliExercise $exercise The exercise to be invoked.
     * @param EventDispatcher $eventDispatcher The event dispatcher.
     */
    public function __construct(CliExercise $exercise, EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->exercise = $exercise;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'CLI Program Runner';
    }

    /**
     * @param string $fileName
     * @param ArrayObject $args
     * @param string $type
     * @return string
     */
    private function executePhpFile($fileName, ArrayObject $args, $type)
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
     * @param ArrayObject $args
     *
     * @return Process
     */
    private function getPhpProcess($fileName, ArrayObject $args)
    {
        $cmd = sprintf('%s %s %s', PHP_BINARY, $fileName, $args->map('escapeshellarg')->implode(' '));
        return new Process($cmd, dirname($fileName), null, null, 10);
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
     * @param string $fileName The absolute path to the student's solution.
     * @return ResultInterface The result of the check.
     */
    public function verify($fileName)
    {
        //arrays are not pass-by-ref
        $args = new ArrayObject($this->exercise->getArgs());

        try {
            $event = $this->eventDispatcher->dispatch(new CliExecuteEvent('cli.verify.reference-execute.pre', $args));
            $solutionOutput = $this->executePhpFile(
                $this->exercise->getSolution()->getEntryPoint(),
                $event->getArgs(),
                'reference'
            );
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(new Event('cli.verify.reference-execute.fail', ['exception' => $e]));
            throw new SolutionExecutionException($e->getMessage());
        }

        try {
            $event = $this->eventDispatcher->dispatch(new CliExecuteEvent('cli.verify.student-execute.pre', $args));
            $userOutput = $this->executePhpFile($fileName, $event->getArgs(), 'student');
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(new Event('cli.verify.student-execute.fail', ['exception' => $e]));
            return Failure::fromNameAndCodeExecutionFailure($this->getName(), $e);
        }
        if ($solutionOutput === $userOutput) {
            return new Success($this->getName());
        }

        return StdOutFailure::fromNameAndOutput($this->getName(), $solutionOutput, $userOutput);
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
     * @param string $fileName The absolute path to the student's solution.
     * @param OutputInterface $output A wrapper around STDOUT.
     * @return bool If the solution was successfully executed, eg. exit code was 0.
     */
    public function run($fileName, OutputInterface $output)
    {
        /** @var CliExecuteEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new CliExecuteEvent('cli.run.student-execute.pre', new ArrayObject($this->exercise->getArgs()))
        );

        $args = $event->getArgs();

        if (count($args)) {
            $glue = max(array_map('strlen', $args->getArrayCopy())) > 30 ? "\n" : ', ';

            $output->writeTitle('Arguments');
            $output->write(implode($glue, $args->getArrayCopy()));
            $output->emptyLine();
        }

        $output->writeTitle("Output");
        $process = $this->getPhpProcess($fileName, $args);
        $process->start();
        $this->eventDispatcher->dispatch(
            new CliExecuteEvent('cli.run.student.executing', $args, ['output' => $output])
        );
        $process->wait(function ($outputType, $outputBuffer) use ($output) {
            $output->writeLine($outputBuffer);
        });

        return $process->isSuccessful();
    }
}
