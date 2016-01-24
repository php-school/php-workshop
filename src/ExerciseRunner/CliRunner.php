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
 * Class CliRunner
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
     * @param CliExercise $exercise
     * @param EventDispatcher $eventDispatcher
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
     * @param string $fileName
     * @return ResultInterface
     */
    public function verify($fileName)
    {
        //arrays are not pass-by-ref
        $args = new ArrayObject($this->exercise->getArgs());

        try {
            $event = $this->eventDispatcher->dispatch(new CliExecuteEvent('cli.verify.solution-execute.pre', $args));
            $solutionOutput = $this->executePhpFile(
                $this->exercise->getSolution()->getEntryPoint(),
                $event->getArgs(),
                'solution'
            );
        } catch (CodeExecutionException $e) {
            $this->eventDispatcher->dispatch(new Event('cli.verify.solution-execute.fail', ['exception' => $e]));
            throw new SolutionExecutionException($e->getMessage());
        }

        try {
            $event = $this->eventDispatcher->dispatch(new CliExecuteEvent('cli.verify.user-execute.pre', $args));
            $userOutput = $this->executePhpFile($fileName, $event->getArgs(), 'user');
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
     * @param string $fileName
     * @param OutputInterface $output
     * @return bool
     */
    public function run($fileName, OutputInterface $output)
    {
        $event = $this->eventDispatcher->dispatch(
            new CliExecuteEvent('cli.run.user-execute.pre', new ArrayObject($this->exercise->getArgs()))
        );

        $process = $this->getPhpProcess($fileName, $event->getArgs());
        $process->run(function ($outputType, $outputBuffer) use ($output) {
            $output->write($outputBuffer);
        });

        return $process->isSuccessful();
    }
}
