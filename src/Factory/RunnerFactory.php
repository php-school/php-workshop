<?php

namespace PhpSchool\PhpWorkshop\Factory;

use Interop\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\ExerciseRunner\CgiRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\CliRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\ExerciseRunnerInterface;

/**
 * Class RunnerFactory
 * @package PhpSchool\PhpWorkshop\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RunnerFactory
{
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
        switch ($exercise->getType()->getValue()) {
            case ExerciseType::CLI:
                $runner = new CliRunner($exercise, $eventDispatcher);
                break;
            case ExerciseType::CGI:
                $runner = new CgiRunner($exercise, $eventDispatcher);
                break;
            default:
                throw new InvalidArgumentException(
                    sprintf('Exercise Type: "%s" not supported', $exercise->getType()->getValue())
                );
        }

        return $runner->configure($exerciseDispatcher);
    }
}
