<?php

namespace PhpSchool\PhpWorkshop\Factory;

use Interop\Container\ContainerInterface;
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
     * @var ContainerInterface
     */
    private $c;

    /**
     * RunnerFactory constructor.
     * @param ContainerInterface $c
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    /**
     * @param ExerciseType $exerciseType
     * @return ExerciseRunnerInterface
     */
    public function create(ExerciseType $exerciseType)
    {
        switch ($exerciseType->getValue()) {
            case ExerciseType::CLI:
                return $this->c->get(CliRunner::class);
            case ExerciseType::CGI:
                return $this->c->get(CgiRunner::class);
        }

        throw new InvalidArgumentException(sprintf('Exercise Type: "%s" not supported', $exerciseType->getValue()));
    }
}
