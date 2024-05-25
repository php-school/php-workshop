<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\TestUtils;

use PhpSchool\PhpWorkshop\Application;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\AbstractExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\ExerciseCheck\ComposerExerciseCheck;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Result\Cgi\CgiResult;
use PhpSchool\PhpWorkshop\Result\Cli\CliResult;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\FailureInterface;
use PhpSchool\PhpWorkshop\Result\ResultGroupInterface;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\Utils\Collection;
use PhpSchool\PhpWorkshop\Utils\Path;
use PhpSchool\PhpWorkshop\Utils\System;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Input\Input;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

use function PhpSchool\PhpWorkshop\collect;

abstract class WorkshopExerciseTest extends TestCase
{
    private Application $app;
    private ContainerInterface $container;
    private ResultAggregator $results;

    private Filesystem $filesystem;
    private string $executionDirectory;

    public const SINGLE_FILE_SOLUTION = 'single-file-solution';
    public const DIRECTORY_SOLUTION = 'directory-solution';

    public function setUp(): void
    {
        $this->app = $this->getApplication();
        $this->container = $this->app->configure();
        $this->filesystem = new Filesystem();
        $this->executionDirectory = System::randomTempDir();
    }

    public function tearDown(): void
    {
        $this->filesystem->remove($this->executionDirectory);
    }

    /**
     * @return class-string
     */
    abstract public function getExerciseClass(): string;

    abstract public function getApplication(): Application;

    protected function getExercise(): ExerciseInterface
    {
        return $this->container->get(ExerciseRepository::class)
            ->findByClassName($this->getExerciseClass());
    }

    public function runExercise(string $submissionFile, string $type = self::SINGLE_FILE_SOLUTION): void
    {
        //we copy the test solution to a random directory
        //so we can properly cleanup. It also saves us from patch crashes.
        $this->filesystem->mkdir($this->executionDirectory);

        $exercise = $this->getExercise();

        $submissionFileAbsolute = sprintf(
            '%s/test/solutions/%s/%s',
            rtrim($this->container->get('basePath'), '/'),
            AbstractExercise::normaliseName($exercise->getName()),
            $submissionFile,
        );

        if (!file_exists($submissionFileAbsolute)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Submission file "%s" does not exist in "%s"',
                    $submissionFile,
                    dirname($submissionFileAbsolute),
                ),
            );
        }

        if ($type === self::SINGLE_FILE_SOLUTION) {
            $this->filesystem->copy($submissionFileAbsolute, Path::join($this->executionDirectory, $submissionFile));
        } else {
            $this->filesystem->mirror(dirname($submissionFileAbsolute), $this->executionDirectory);
        }

        $submissionFileAbsolute = Path::join($this->executionDirectory, basename($submissionFile));

        if ($exercise instanceof ComposerExerciseCheck) {
            $this->installDeps($exercise, dirname($submissionFileAbsolute));
        }

        $input = new Input($this->container->get('appName'), [
            'program' => $submissionFileAbsolute,
        ]);

        $this->results = $this->container->make(ExerciseDispatcher::class)
            ->verify($exercise, $input);
    }

    /**
     * @param ExerciseInterface&ProvidesSolution $exercise
     * @param string $directory
     */
    private function installDeps(ExerciseInterface $exercise, string $directory): void
    {
        if (file_exists("$directory/composer.json") && !file_exists("$directory/vendor")) {
            $execFinder = new ExecutableFinder();
            $execFinder->addSuffix('.phar');

            $process = new Process(
                [$execFinder->find('composer'), 'install', '--no-interaction'],
                $directory,
            );
            $process->mustRun();
        }
    }

    public function assertVerifyWasSuccessful(): void
    {
        $failures = (new Collection($this->results->getIterator()->getArrayCopy()))
            ->filter(function (ResultInterface $result) {
                return $result instanceof FailureInterface;
            })
            ->map(function (FailureInterface $failure) {
                return sprintf(
                    '  * %s%s',
                    get_class($failure),
                    $failure instanceof Failure ? ": {$failure->getReason()}" : '',
                );
            });

        $help = $failures->isEmpty()
            ? ""
            : sprintf("\n\nAll Failures:\n\n%s\n", $failures->implode("\n"));

        $this->assertTrue($this->results->isSuccessful(), $help);
    }

    public function assertVerifyWasNotSuccessful(): void
    {
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

        $allFailures = (new Collection($this->results->getIterator()->getArrayCopy()))
            ->filter(function (ResultInterface $result) {
                return $result instanceof FailureInterface;
            })
            ->map(function (FailureInterface $failure) {
                return sprintf(
                    '  * %s%s',
                    get_class($failure),
                    $failure instanceof Failure ? ": {$failure->getReason()}" : '',
                );
            });

        $help = $allFailures->isEmpty()
            ? ""
            : sprintf("\n\nAll Failures:\n\n%s\n", $allFailures->implode("\n"));

        $this->assertCount(1, $failures, "No failure with reason: '$reason' . $help");
    }

    public function assertOutputWasIncorrect(): void
    {
        $this->assertFalse($this->getOutputResult()->isSuccessful());
    }

    public function assertOutputWasCorrect(): void
    {
        $this->assertTrue($this->getOutputResult()->isSuccessful());
    }

    public function getOutputResult(): ResultGroupInterface
    {
        $exerciseType = $this->getExercise()->getType();

        if ($exerciseType->equals(ExerciseType::CLI())) {
            $results = collect($this->results->getIterator()->getArrayCopy())
                ->filter(function (ResultInterface $result) {
                    return $result instanceof CliResult;
                });

            $this->assertCount(1, $results);
        }

        if ($exerciseType->equals(ExerciseType::CGI())) {
            $results = collect($this->results->getIterator()->getArrayCopy())
                ->filter(function (ResultInterface $result) {
                    return $result instanceof CgiResult;
                });

            $this->assertCount(1, $results);
        }

        return $results->values()->get(0);
    }

    public function assertResultsHasFailureAndMatches(string $resultClass, callable $matcher): void
    {
        $failures = collect($this->results->getIterator()->getArrayCopy())
            ->filter(function (ResultInterface $result) {
                return $result instanceof FailureInterface;
            })
            ->filter(function (FailureInterface $failure) use ($resultClass) {
                return $failure instanceof $resultClass;
            });

        if ($failures->count() === 0) {
            throw new ExpectationFailedException("No failures found for class: '$resultClass'");
        }

        $failures->each(function ($failure) use ($matcher) {
            $this->assertTrue($matcher($failure));
        });
    }
}
