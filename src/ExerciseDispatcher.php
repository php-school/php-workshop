<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop;

use PhpSchool\PhpWorkshop\Check\CheckRepository;
use PhpSchool\PhpWorkshop\Check\ListenableCheckInterface;
use PhpSchool\PhpWorkshop\Check\PhpLintCheck;
use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exception\CheckNotApplicableException;
use PhpSchool\PhpWorkshop\Exception\ExerciseNotConfiguredException;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContextFactory;
use PhpSchool\PhpWorkshop\ExerciseRunner\RunnerManager;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Result\FailureInterface;
use PhpSchool\PhpWorkshop\Exception\CouldNotRunException;

/**
 * This class is used to verify/run a student's solution to an exercise. It routes to the correct
 * runner based on the exercise type.
 */
class ExerciseDispatcher
{
    /**
     * @var array<SimpleCheckInterface>
     */
    private array $checksToRunBefore = [];

    /**
     * @var array<SimpleCheckInterface>
     */
    private array $checksToRunAfter = [];


    /**
     * @param RunnerManager $runnerManager Factory capable of building an exercise runner based on the exercise type.
     * @param ResultAggregator $results
     * @param EventDispatcher $eventDispatcher
     * @param CheckRepository $checkRepository
     */
    public function __construct(
        private RunnerManager $runnerManager,
        private ResultAggregator $results,
        private EventDispatcher $eventDispatcher,
        private CheckRepository $checkRepository,
        private ExecutionContextFactory $executionContextFactory,
    ) {
    }

    /**
     * Queue a specific check to be run when the exercise is verified. When the exercise is verified
     * the check specified as the first argument will also be executed. Throws an `InvalidArgumentException`
     * if the check does not exist in the `CheckRepository`.
     *
     * @param class-string $requiredCheck The name of the required check.
     * @throws InvalidArgumentException If the check does not exist.
     */
    public function requireCheck(string $requiredCheck): void
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
     * @param Input $input The command line arguments passed to the command.
     * @return ResultAggregator Contains all the results injected via the runner, checks and events.
     * @throws CheckNotApplicableException If the check is not applicable to the exercise type.
     * @throws ExerciseNotConfiguredException If the exercise does not implement the correct interface based on
     * the checks required.
     */
    public function verify(ExerciseInterface $exercise, Input $input): ResultAggregator
    {
        $context = $this->executionContextFactory->fromInputAndExercise($input, $exercise);
        $runner = $this->runnerManager->getRunner($exercise);

        $exercise->defineListeners($this->eventDispatcher);

        foreach ([...$runner->getRequiredChecks(), ...$exercise->getRequiredChecks()] as $requiredCheck) {
            $this->requireCheck($requiredCheck);
        }

        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('verify.start', $exercise, $input));

        $this->validateChecks($this->checksToRunBefore, $exercise);
        $this->validateChecks($this->checksToRunAfter, $exercise);

        foreach ($this->checksToRunBefore as $check) {
            $this->results->add($check->check($context));

            if (!$this->results->isSuccessful()) {
                return $this->results;
            }
        }

        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('verify.pre.execute', $exercise, $input));

        try {
            $this->results->add($runner->verify($context));
        } finally {
            $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('verify.post.execute', $exercise, $input));
        }

        foreach ($this->checksToRunAfter as $check) {
            $this->results->add($check->check($context));
        }

        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('verify.post.check', $exercise, $input));
        $exercise->tearDown();

        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('verify.finish', $exercise, $input));
        return $this->results;
    }

    /**
     * Run a student's solution against a specific exercise. Does not invoke checks. Invokes the
     * correct runner for the exercise based on the exercise type. Various events are triggered throughout the process.
     * The output of the solution is written directly to the `OutputInterface` instance.
     *
     * @param ExerciseInterface $exercise The exercise instance.
     * @param Input $input The command line arguments passed to the command.
     * @param OutputInterface $output An output instance capable of writing to stdout.
     * @return bool Whether the solution ran successfully or not.
     */
    public function run(ExerciseInterface $exercise, Input $input, OutputInterface $output): bool
    {
        $context = $this->executionContextFactory->fromInputAndExercise($input, $exercise);

        $exercise->defineListeners($this->eventDispatcher);

        /** @var PhpLintCheck $lint */
        $lint = $this->checkRepository->getByClass(PhpLintCheck::class);
        $result = $lint->check($context);

        if ($result instanceof FailureInterface) {
            throw CouldNotRunException::fromFailure($result);
        }

        $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('run.start', $exercise, $input));

        try {
            $exitStatus = $this->runnerManager
                ->getRunner($exercise)
                ->run($context, $output);
        } finally {
            $this->eventDispatcher->dispatch(new ExerciseRunnerEvent('run.finish', $exercise, $input));
        }

        return $exitStatus;
    }

    /**
     * @param SimpleCheckInterface[] $checks
     * @param ExerciseInterface $exercise
     * @throws CheckNotApplicableException
     * @throws ExerciseNotConfiguredException
     */
    private function validateChecks(array $checks, ExerciseInterface $exercise): void
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
    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    /**
     * @return SimpleCheckInterface[]
     */
    public function getChecksToRunBefore(): array
    {
        return $this->checksToRunBefore;
    }

    /**
     * @return SimpleCheckInterface[]
     */
    public function getChecksToRunAfter(): array
    {
        return $this->checksToRunAfter;
    }
}
