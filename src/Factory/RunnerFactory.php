<?php

namespace PhpSchool\PhpWorkshop\Factory;

use Interop\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\CgiExercise;
use PhpSchool\PhpWorkshop\Exercise\CliExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Exercise\ExtExercise;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\ExerciseRunner\CgiRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\CliRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\CustomRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\ExerciseRunnerInterface;
use PhpSchool\PhpWorkshop\ExerciseRunner\ExtRunner;

/**
 * Class RunnerFactory
 * @package PhpSchool\PhpWorkshop\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RunnerFactory
{

    /**
     * @var array
     */
    private $supportedTypes = [
        ExerciseType::CLI,
        ExerciseType::CGI,
        ExerciseType::CUSTOM,
    ];

    /**
     * @param ExerciseInterface $exercise
     * @param EventDispatcher $eventDispatcher
     * @param ExerciseDispatcher $exerciseDispatcher
     * @return ExerciseRunnerInterface
     */
    public function create(
        ExerciseInterface $exercise,
        EventDispatcher $eventDispatcher,
        ExerciseDispatcher $exerciseDispatcher
    ) {

        $type = $exercise->getType();

        if (!in_array($type, $this->supportedTypes)) {
            throw new InvalidArgumentException(
                sprintf('Exercise Type: "%s" not supported', $exercise->getType()->getValue())
            );
        }

        $requiredInterface = $type->getExerciseInterface();

        if (!$exercise instanceof $requiredInterface) {
            throw InvalidArgumentException::missingImplements($exercise, $requiredInterface);
        }

        switch ($type->getValue()) {
            case ExerciseType::CLI:
                $runner = new CliRunner($exercise, $eventDispatcher);
                break;
            case ExerciseType::CGI:
                $runner = new CgiRunner($exercise, $eventDispatcher);
                break;
            case ExerciseType::CUSTOM:
                $runner = new CustomRunner($exercise);
                break;
        }

        /** @noinspection PhpUndefinedVariableInspection */
        return $runner->configure($exerciseDispatcher);
    }
}
