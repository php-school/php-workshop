<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Exercise\CgiExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
use Psr\Http\Message\RequestInterface;

class CgiExerciseImpl implements ExerciseInterface, CgiExercise
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name = 'my-exercise')
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->name;
    }

    public function getSolution(): SolutionInterface
    {
        // TODO: Implement getSolution() method.
    }

    public function getProblem(): string
    {
        // TODO: Implement getProblem() method.
    }

    public function tearDown(): void
    {
        // TODO: Implement tearDown() method.
    }

    /**
     * This method should return an array of PSR-7 requests, which will be forwarded to the student's
     * solution.
     *
     * @return RequestInterface[] An array of PSR-7 requests.
     */
    public function getRequests(): array
    {
        return []; // TODO: Implement getRequests() method.
    }

    public function getType(): ExerciseType
    {
        return ExerciseType::CGI();
    }

    public function configure(ExerciseDispatcher $dispatcher): void
    {
    }
}
