<?php

namespace PhpSchool\PhpWorkshop\Factory;

use Interop\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
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
     * @param ExerciseType $exerciseType
     * @param EventDispatcher $eventDispatcher
     * @return ExerciseRunnerInterface
     */
    public function create(ExerciseType $exerciseType, EventDispatcher $eventDispatcher)
    {
        switch ($exerciseType->getValue()) {
            case ExerciseType::CLI:
                return new CliRunner($eventDispatcher);
            case ExerciseType::CGI:
                return new CgiRunner($eventDispatcher);
        }

        throw new InvalidArgumentException(sprintf('Exercise Type: "%s" not supported', $exerciseType->getValue()));
    }
}
