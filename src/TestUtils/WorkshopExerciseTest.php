<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\TestUtils;

use PhpSchool\PhpWorkshop\Application;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\AbstractExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Result\Cgi\CgiResult;
use PhpSchool\PhpWorkshop\Result\Cli\CliResult;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\FailureInterface;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\Utils\Collection;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Input\Input;

abstract class WorkshopExerciseTest extends TestCase
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ResultAggregator
     */
    protected $results;

    public function setUp(): void
    {
        $this->app = $this->getApplication();
        $this->container = $this->app->configure();
    }

    /**
     * @return class-string
     */
    abstract public function getExerciseClass(): string;

    abstract public function getApplication(): Application;

    private function getExercise(): ExerciseInterface
    {
        return $this->container->get(ExerciseRepository::class)
            ->findByClassName($this->getExerciseClass());
    }

    public function runExercise(string $submissionFile)
    {
        $exercise = $this->getExercise();

        $submissionFileAbsolute = sprintf(
            '%s/test/solutions/%s/%s',
            rtrim($this->container->get('basePath'), '/'),
            AbstractExercise::normaliseName($exercise->getName()),
            $submissionFile
        );

        if (!file_exists($submissionFileAbsolute)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Submission file "%s" does not exist in "%s"',
                    $submissionFile,
                    dirname($submissionFileAbsolute)
                )
            );
        }

        $input = new Input($this->container->get('appName'), [
            'program' => $submissionFileAbsolute
        ]);

        $this->results = $this->container->get(ExerciseDispatcher::class)
            ->verify($exercise, $input);
    }

    public function assertVerifyWasSuccessful(): void
    {
        $failures = (new Collection($this->results->getIterator()->getArrayCopy()))
            ->filter(function (ResultInterface $result) {
                return $result instanceof FailureInterface;
            })
            ->map(function (Failure $failure) {
                return $failure->getReason();
            })
            ->implode(', ');


        $this->assertTrue($this->results->isSuccessful(), $failures);
    }

    public function assertVerifyWasNotSuccessful(): void
    {
        $failures = (new Collection($this->results->getIterator()->getArrayCopy()))
            ->filter(function (ResultInterface $result) {
                return $result instanceof FailureInterface;
            })
            ->map(function (FailureInterface $failure) {
                return get_class($failure);
            })
            ->implode(', ');

        $this->assertFalse($this->results->isSuccessful());
    }

    public function assertResultCount(int $count): void
    {
        $this->assertCount($count, $this->results);
    }

    public function assertResultsHasFailure(string $resultClass, string $reason): void
    {
        $failures = (new Collection($this->results->getIterator()->getArrayCopy()))
            ->filter(function (ResultInterface $result) {
                return $result instanceof Failure;
            })
            ->filter(function (Failure $failure) use ($reason) {
                return $failure->getReason() === $reason;
            });

        $this->assertCount(1, $failures, "No failure with reason: '$reason'");
    }

    public function assertOutputWasIncorrect(): void
    {
        $exerciseType = $this->getExercise()->getType();

        if ($exerciseType->equals(ExerciseType::CLI())) {
            $results = (new Collection($this->results->getIterator()->getArrayCopy()))
                ->filter(function (ResultInterface $result) {
                    return $result instanceof CliResult;
                });

            $this->assertCount(1, $results);
        }

        if ($exerciseType->equals(ExerciseType::CGI())) {
            $results = (new Collection($this->results->getIterator()->getArrayCopy()))
                ->filter(function (ResultInterface $result) {
                    return $result instanceof CgiResult;
                });

            $this->assertCount(1, $results);
        }

        $outputResults = $results->values()->get(0);

        $this->assertFalse($outputResults->isSuccessful());
    }
}
