<?php

namespace PhpSchool\PhpWorkshop;

use Assert\Assertion;
use PhpSchool\PhpWorkshop\Check\CheckCollection;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Check\CheckRepository;
use PhpSchool\PhpWorkshop\Check\ListenableCheckInterface;
use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\CheckNotApplicableException;
use PhpSchool\PhpWorkshop\Exception\ExerciseNotConfiguredException;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Factory\RunnerFactory;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * This class is used to verify/run a student's solution to an exercise. It routes to the correct
 * runner based on the exercise type.
 *
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseDispatcher
{
    /**
     * @var CheckInterface[]
     */
    private $checksToRunBefore = [];

    /**
     * @var CheckInterface[]
     */
    private $checksToRunAfter = [];

    /**
     * @var RunnerFactory
     */
    private $runnerFactory;

    /**
     * @var ResultAggregator
     */
    private $results;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var CheckRepository
     */
    private $checkRepository;

    /**
     * @param RunnerFactory $runnerFactory Factory capable of building an exercise runner based on the exercise type.
     * @param ResultAggregator $resultAggregator
     * @param EventDispatcher $eventDispatcher
     * @param CheckRepository $checkRepository
     */
    public function __construct(
        RunnerFactory $runnerFactory,
        ResultAggregator $resultAggregator,
        EventDispatcher $eventDispatcher,
        CheckRepository $checkRepository
    ) {
        $this->runnerFactory    = $runnerFactory;
        $this->results          = $resultAggregator;
        $this->eventDispatcher  = $eventDispatcher;
        $this->checkRepository  = $checkRepository;
    }

    /**
     * Queue a specific check to be run when the exercise is verified. When the exercise is verified
     * the check specified as the first argument will also be executed. Throws an `InvalidArgumentException`
     * if the check does not exist in the `CheckRepository`.
     *
     * @param string $requiredCheck The name of the required check.
     * @throws InvalidArgumentException If the check does not exist.
     */
    public function requireCheck($requiredCheck)
    {
        if (!$this->checkRepository->has($requiredCheck)) {
            throw new InvalidArgumentException(sprintf('Check: "%s" does not exist', $requiredCheck));
        }

        $check = $this->checkRepository->getByClass($requiredCheck);

        if ($check instanceof SimpleCheckInterface) {
            switch ($check->getPosition()) {
                case SimpleCheckInterface::CHECK_BEFORE:
                    $this->checksToRunBefore[] = $check;
                    break;
                case SimpleCheckInterface::CHECK_AFTER:
                    $this->checksToRunAfter[] = $check;
                    break;
                default:
                    throw InvalidArgumentException::notValidParameter(
                        'position',
                        [SimpleCheckInterface::CHECK_BEFORE, SimpleCheckInterface::CHECK_AFTER],
                        $check->getPosition()
                    );
            }

            return;
        }

        if (!$check instanceof ListenableCheckInterface) {
            throw new InvalidArgumentException(sprintf('Check: "%s" is not a listenable check', $requiredCheck));
        }

        $check->attach($this->eventDispatcher);
    }

    /**
     * Verify a students solution against a specific exercise. Runs queued checks based on their position. Invokes the
     * correct runner for the exercise based on the exercise type. Various events are triggered throughout the process.
     *
     * @param ExerciseInterface $exercise The exercise instance.
     * @param string $fileName The absolute file name of the students solution.
     * @return ResultAggregator Contains all the results injected via the runner, checks and events.
     * @throws CheckNotApplicableException If the check is not applicable to the exercise type.
     * @throws ExerciseNotConfiguredException If the exercise does not implement the correct interface based on
     * the checks required.
     */
    public function verify(ExerciseInterface $exercise, $fileName)
    {
        $exercise->configure($this);

        $runner = $this->runnerFactory->create($exercise, $this->eventDispatcher);
        $this->eventDispatcher->dispatch(new Event('verify.start', compact('exercise', 'fileName')));

        $this->validateChecks($this->checksToRunBefore, $exercise);
        $this->validateChecks($this->checksToRunAfter, $exercise);

        foreach ($this->checksToRunBefore as $check) {
            $this->results->add($check->check($exercise, $fileName));

            if (!$this->results->isSuccessful()) {
                return $this->results;
            }
        }

        $this->eventDispatcher->dispatch(new Event('verify.pre.execute', compact('exercise', 'fileName')));

        try {
            $this->results->add($runner->verify($fileName));
        } finally {
            $this->eventDispatcher->dispatch(new Event('verify.post.execute', compact('exercise', 'fileName')));
        }

        foreach ($this->checksToRunAfter as $check) {
            $this->results->add($check->check($exercise, $fileName));
        }

        $this->eventDispatcher->dispatch(new Event('verify.post.check', compact('exercise', 'fileName')));
        $exercise->tearDown();

        $this->eventDispatcher->dispatch(new Event('verify.finish', compact('exercise', 'fileName')));
        return $this->results;
    }

    /**
     * Run a student's solution against a specific exercise. Does not invoke checks. Invokes the
     * correct runner for the exercise based on the exercise type. Various events are triggered throughout the process.
     * The output of the solution is written directly to the `OutputInterface` instance.
     *
     * @param ExerciseInterface $exercise The exercise instance.
     * @param string $fileName The absolute file name of the students solution.
     * @param OutputInterface $output An output instance capable of writing to stdout.
     * @return bool Whether the solution ran successfully or not.
     */
    public function run(ExerciseInterface $exercise, $fileName, OutputInterface $output)
    {
        $exercise->configure($this);
        $this->eventDispatcher->dispatch(new Event('run.start', compact('exercise', 'fileName')));

        try {
            $exitStatus = $this->runnerFactory
                ->create($exercise, $this->eventDispatcher)
                ->run($fileName, $output);
        } finally {
            $this->eventDispatcher->dispatch(new Event('run.finish', compact('exercise', 'fileName')));
        }

        return $exitStatus;
    }

    /**
     * @param CheckInterface[] $checks
     * @param ExerciseInterface $exercise
     * @throws CheckNotApplicableException
     * @throws ExerciseNotConfiguredException
     */
    private function validateChecks(array $checks, ExerciseInterface $exercise)
    {
        foreach ($checks as $check) {
            if (!$check->canRun($exercise->getType())) {
                throw CheckNotApplicableException::fromCheckAndExercise($check, $exercise);
            }

            $checkInterface = $check->getExerciseInterface();
            if (!$exercise instanceof $checkInterface) {
                throw ExerciseNotConfiguredException::missingImplements($exercise, $checkInterface);
            }
        }
    }

    /**
     * Retrieve the `EventDispatcher` instance.
     *
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }
}
