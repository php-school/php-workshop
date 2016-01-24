<?php

namespace PhpSchool\PhpWorkshop;

use Assert\Assertion;
use PhpSchool\PhpWorkshop\Check\CheckCollection;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Check\CheckRepository;
use PhpSchool\PhpWorkshop\Check\ListenableCheckInterface;
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
 * Class ExerciseDispatcher
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseDispatcher
{
    const CHECK_BEFORE = 'before';
    const CHECK_AFTER = 'after';

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
     * @param RunnerFactory $runnerFactory
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
     * @param string $requiredCheck
     * @param string $position
     * @throws InvalidArgumentException
     */
    public function requireCheck($requiredCheck, $position)
    {
        if (!$this->checkRepository->has($requiredCheck)) {
            throw new InvalidArgumentException(sprintf('Check: "%s" does not exist', $requiredCheck));
        }

        switch ($position) {
            case static::CHECK_BEFORE:
                $this->checksToRunBefore[] = $this->checkRepository->getByClass($requiredCheck);
                break;
            case static::CHECK_AFTER:
                $this->checksToRunAfter[] = $this->checkRepository->getByClass($requiredCheck);
                break;
            default:
                throw InvalidArgumentException::notValidParameter(
                    'position',
                    [static::CHECK_BEFORE, static::CHECK_AFTER],
                    $position
                );
        }
    }

    /**
     * @param string $requiredCheck
     * @throws InvalidArgumentException
     */
    public function requireListenableCheck($requiredCheck)
    {
        if (!$this->checkRepository->has($requiredCheck)) {
            throw new InvalidArgumentException(sprintf('Check: "%s" does not exist', $requiredCheck));
        }

        $check = $this->checkRepository->getByClass($requiredCheck);

        if (!$check instanceof ListenableCheckInterface) {
            throw new InvalidArgumentException(sprintf('Check: "%s" is not a listenable check', $requiredCheck));
        }

        $check->attach($this->eventDispatcher);
    }

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return ResultAggregator
     * @throws CheckNotApplicableException
     * @throws ExerciseNotConfiguredException
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
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @param OutputInterface $output
     * @return bool
     */
    public function run(ExerciseInterface $exercise, $fileName, OutputInterface $output)
    {
        $this->eventDispatcher->dispatch(new Event('run.start', compact('exercise', 'fileName')));

        $exitStatus = $this->runnerFactory
            ->create($exercise, $this->eventDispatcher)
            ->run($fileName, $output);

        $this->eventDispatcher->dispatch(new Event('run.finish', compact('exercise', 'fileName')));
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
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }
}
