<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner;

use PhpSchool\PhpWorkshop\Event\CliEvent;
use PhpSchool\PhpWorkshop\Event\CliExecuteEvent;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
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
 * Class CliRunner
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CliRunner implements ExerciseRunnerInterface
{

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
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
     * @return string
     */
    private function executePhpFile($fileName, ArrayObject $args)
    {
        $process = $this->getPhpProcess($fileName, $args);
        $process->run();

        if (!$process->isSuccessful()) {
            throw CodeExecutionException::fromProcess($process);
        }
        
        return $process->getOutput();
    }

    /**
     * @param string      $fileName
     * @param ArrayObject $args
     *
     * @return Process
     */
    private function getPhpProcess($fileName, ArrayObject $args)
    {
        $cmd = sprintf('%s %s %s', PHP_BINARY, $fileName, $args->map('escapeshellarg')->implode(' '));
        return new Process($cmd, dirname($fileName));
    }

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return ResultInterface
     */
    public function verify(ExerciseInterface $exercise, $fileName)
    {
        if ($exercise->getType()->getValue() !== ExerciseType::CLI) {
            throw new \InvalidArgumentException;
        }

        //arrays are not pass-by-ref
        $args = new ArrayObject($exercise->getArgs());

        try {
            $event = $this->eventDispatcher->dispatch(new CliExecuteEvent('cli.verify.solution-execute.pre', $args));
            $solutionOutput = $this->executePhpFile($exercise->getSolution()->getEntryPoint(), $event->getArgs());
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(new Event('cli.verify.solution-execute.fail', ['exception' => $e]));
            throw new SolutionExecutionException($e->getMessage());
        }

        try {
            $event = $this->eventDispatcher->dispatch(new CliExecuteEvent('cli.verify.user-execute.pre', $args));
            $userOutput = $this->executePhpFile($fileName, $event->getArgs());
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(new Event('cli.verify.user-execute.fail', ['exception' => $e]));
            return Failure::fromNameAndCodeExecutionFailure($this->getName(), $e);
        }
        if ($solutionOutput === $userOutput) {
            return new Success($this->getName());
        }

        return StdOutFailure::fromNameAndOutput($this->getName(), $solutionOutput, $userOutput);
    }

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @param OutputInterface $output
     * @return bool
     */
    public function run(ExerciseInterface $exercise, $fileName, OutputInterface $output)
    {
        if ($exercise->getType()->getValue() !== ExerciseType::CLI) {
            throw new \InvalidArgumentException;
        }

        $event = $this->eventDispatcher->dispatch(
            new CliExecuteEvent('cli.run.user-execute.pre', new ArrayObject($exercise->getArgs()))
        );

        $process = $this->getPhpProcess($fileName, $event->getArgs());
        $process->run(function ($outputType, $outputBuffer) use ($output) {
            $output->write($outputBuffer);
        });

        return $process->isSuccessful();
    }
}
