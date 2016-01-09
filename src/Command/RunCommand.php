<?php

namespace PhpSchool\PhpWorkshop\Command;

use PhpSchool\PhpWorkshop\CodePatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\CgiOutputExerciseCheck;
use PhpSchool\PhpWorkshop\ExerciseCheck\StdOutExerciseCheck;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Output;
use PhpSchool\PhpWorkshop\ProcessExecutor\CgiProcessExecutor;
use PhpSchool\PhpWorkshop\ProcessExecutor\CliProcessExecutor;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshop\UserStateSerializer;

/**
 * Class VerifyCommand
 * @package PhpSchool\PhpWorkshop\Command
 * @author Michael Woodward <mikeymike.mw@gmail.com>
 */
class RunCommand
{
    /**
     * @var Output
     */
    private $output;

    /**
     * @var ExerciseRepository
     */
    private $exerciseRepository;

    /**
     * @var UserState
     */
    private $userState;

    /**
     * @var UserStateSerializer
     */
    private $userStateSerializer;

    /**
     * @var CodePatcher
     */
    private $patcher;

    /**
     * @param ExerciseRepository  $exerciseRepository
     * @param UserState           $userState
     * @param UserStateSerializer $userStateSerializer
     * @param Output              $output
     * @param CodePatcher         $patcher
     */
    public function __construct(
        ExerciseRepository $exerciseRepository,
        UserState $userState,
        UserStateSerializer $userStateSerializer,
        Output $output,
        CodePatcher $patcher
    ) {
        $this->output               = $output;
        $this->exerciseRepository   = $exerciseRepository;
        $this->userState            = $userState;
        $this->userStateSerializer  = $userStateSerializer;
        $this->patcher              = $patcher;
    }

    /**
     * @param string $appName
     * @param string $program
     *
     * @return int|void
     */
    public function __invoke($appName, $program)
    {
        if (!file_exists($program)) {
            $this->output->printError(
                sprintf('Could not run. File: "%s" does not exist', $program)
            );
            return 1;
        }
        $program = realpath($program);

        if (!$this->userState->isAssignedExercise()) {
            $this->output->printError("No active exercises. Select one from the menu");
            return 1;
        }

        $exercise = $this->exerciseRepository->findByName($this->userState->getCurrentExercise());

        if (!$exercise->hasOutput()) {
            $this->output->printInfo("There is no output for this exercise.");
            return 1;
        }

        try {
            $originalCode = file_get_contents($program);
            file_put_contents($program, $this->patcher->patch($exercise, $originalCode));

            if ($exercise instanceof CgiOutputExerciseCheck) {
                $this->processCgiExercise($exercise, $program);
            } elseif ($exercise instanceof StdOutExerciseCheck) {
                $this->processCliExercise($exercise, $program);
            }
        } catch (\Exception $e) {
            $this->output->printError('ERROR!!!');
            $this->output->writeLine($e->getMessage());
            $this->output->printInfo('See the trace for more details...');
            $this->output->writeLine($e->getTraceAsString());
        } finally {
            file_put_contents($program, $originalCode);
        }
    }

    /**
     * @param CgiOutputExerciseCheck $exercise
     * @param string                 $program
     */
    private function processCgiExercise(CgiOutputExerciseCheck $exercise, $program)
    {
        $requests = $exercise->getRequests();

        foreach ($requests as $count => $request) {
            $executor = new CgiProcessExecutor($request);

            $this->output->printInfo(sprintf('Request %d', $count+1));
            $this->output->writeLine(sprintf('Method: %s', $request->getMethod()));
            $this->output->writeLines(array_map(function ($value, $header) {
                return sprintf('%s: %s', $header, implode(' ', $value));
            }, $request->getHeaders(), array_keys($request->getHeaders())));
            $this->output->writeLine(sprintf('Body: %s', $request->getBody()));

            $this->output->printSuccess(sprintf('Result %d', $count+1));
            $this->output->writeLine($executor->executePhpFile($program));
            $this->output->writeLine('');
        }
    }

    /**
     * @param ExerciseInterface $exercise
     * @param string            $program
     */
    private function processCliExercise(ExerciseInterface $exercise, $program)
    {
        $executor = new CliProcessExecutor($exercise->getArgs());

        $this->output->printInfo('Arguments');
        foreach ($exercise->getArgs() as $count => $arg) {
            $this->output->writeLine(sprintf('$argv[%d]: %s', $count+1, $arg));
        }

        $this->output->printSuccess('Result');
        $this->output->writeLine(($executor->executePhpFile($program)));
    }
}
