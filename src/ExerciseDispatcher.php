<?php

namespace PhpSchool\PhpWorkshop;

use Assert\Assertion;
use PhpSchool\PhpWorkshop\Check\CheckCollection;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Check\CheckRepository;
use PhpSchool\PhpWorkshop\Exception\CheckNotApplicableException;
use PhpSchool\PhpWorkshop\Exception\ExerciseNotConfiguredException;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\SelfCheck;
use PhpSchool\PhpWorkshop\ExerciseRunner\ExerciseRunnerInterface;
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
     * @var CheckRepository
     */
    private $checkRepository;

    /**
     * @var CheckInterface[]
     */
    private $checksToRunBefore = [];

    /**
     * @var CheckInterface[]
     */
    private $checksToRunAfter = [];

    /**
     * @var CodePatcher
     */
    private $codePatcher;
    /**
     * @var RunnerFactory
     */
    private $runnerFactory;

    /**
     * @param RunnerFactory $runnerFactory
     * @param CheckRepository $checkRepository
     * @param CodePatcher $codePatcher
     */
    public function __construct(RunnerFactory $runnerFactory, CheckRepository $checkRepository, CodePatcher $codePatcher)
    {
        $this->checkRepository  = $checkRepository;
        $this->codePatcher      = $codePatcher;
        $this->runnerFactory    = $runnerFactory;
    }

    /**
     * @param string $requiredCheck
     * @param $position
     * @throws CheckNotExistsException
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
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return ResultAggregator
     * @throws CheckNotApplicableException
     * @throws ExerciseNotConfiguredException
     */
    public function verify(ExerciseInterface $exercise, $fileName)
    {
        $runner = $this->runnerFactory->create($exercise->getType());

        $exercise->configure($this);

        $resultAggregator = new ResultAggregator;

        $this->validateChecks($this->checksToRunBefore, $exercise);
        $this->validateChecks($this->checksToRunAfter, $exercise);

        foreach ($this->checksToRunBefore as $check) {
            $resultAggregator->add($check->check($exercise, $fileName));

            if (!$resultAggregator->isSuccessful()) {
                return $resultAggregator;
            }
        }

        //patch code
        //pre-check takes care of checking that code can be parsed correctly
        //if not it would have returned already with a failure
        $originalCode = file_get_contents($fileName);
        file_put_contents($fileName, $this->codePatcher->patch($exercise, $originalCode));

        try {
            $resultAggregator->add($runner->verify($exercise, $fileName));

            foreach ($this->checksToRunAfter as $check) {
                $resultAggregator->add($check->check($exercise, $fileName));
            }

            //self check, for easy custom checking
            if ($exercise instanceof SelfCheck) {
                $resultAggregator->add($exercise->check($fileName));
            }

            $exercise->tearDown();
        } finally {
            //put back actual code, to remove patched additions
            file_put_contents($fileName, $originalCode);
        }

        return $resultAggregator;
    }

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @param OutputInterface $output
     * @return bool
     */
    public function run(ExerciseInterface $exercise, $fileName, OutputInterface $output)
    {
        return $this->runnerFactory
            ->create($exercise->getType())
            ->run($exercise, $fileName, $output);
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
}
