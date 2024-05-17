<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Check\CheckRepository;
use PhpSchool\PhpWorkshop\Check\ListenableCheckInterface;
use PhpSchool\PhpWorkshop\Check\PhpLintCheck;
use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Event\EventInterface;
use PhpSchool\PhpWorkshop\Exception\CheckNotApplicableException;
use PhpSchool\PhpWorkshop\Exception\ExerciseNotConfiguredException;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContextFactory;
use PhpSchool\PhpWorkshop\ExerciseRunner\ExerciseRunnerInterface;
use PhpSchool\PhpWorkshop\ExerciseRunner\RunnerManager;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

class ExerciseDispatcherTest extends TestCase
{
    private Filesystem $filesystem;
    private string $file;

    public function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->file = sprintf('%s/%s/submission.php', str_replace('\\', '/', sys_get_temp_dir()), $this->getName());
        mkdir(dirname($this->file), 0775, true);
        touch($this->file);
    }

    public function testGetEventDispatcher(): void
    {
        $eventDispatcher = new EventDispatcher($results = new ResultAggregator());

        $exerciseDispatcher = new ExerciseDispatcher(
            $this->createMock(RunnerManager::class),
            $results,
            $eventDispatcher,
            new CheckRepository(),
        );

        $this->assertSame($eventDispatcher, $exerciseDispatcher->getEventDispatcher());
    }

    public function testRequireCheckThrowsExceptionIfCheckDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Check: "NotACheck" does not exist');

        $exerciseDispatcher = new ExerciseDispatcher(
            $this->createMock(RunnerManager::class),
            new ResultAggregator(),
            new EventDispatcher(new ResultAggregator()),
            new CheckRepository(),
        );
        $exerciseDispatcher->requireCheck('NotACheck');
    }

    public function testRequireCheckThrowsExceptionIfPositionNotValid(): void
    {
        $check = $this->createMock(SimpleCheckInterface::class);
        $check->method('getName')->willReturn('Some Check');
        $check->method('getPosition')->willReturn('middle');

        $exerciseDispatcher = new ExerciseDispatcher(
            $this->createMock(RunnerManager::class),
            new ResultAggregator(),
            new EventDispatcher(new ResultAggregator()),
            new CheckRepository([$check]),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter: "position" can only be one of: "before", "after" Received: "middle"');
        $exerciseDispatcher->requireCheck(get_class($check));
    }

    public function testRequireBeforeCheckIsCorrectlyRegistered(): void
    {
        $check = $this->createMock(SimpleCheckInterface::class);
        $check->method('getName')->willReturn('Some Check');
        $check->method('getPosition')->willReturn(SimpleCheckInterface::CHECK_BEFORE);

        $exerciseDispatcher = new ExerciseDispatcher(
            $this->createMock(RunnerManager::class),
            new ResultAggregator(),
            new EventDispatcher(new ResultAggregator()),
            new CheckRepository([$check]),
        );

        $exerciseDispatcher->requireCheck(get_class($check));
        $this->assertEquals([$check], $exerciseDispatcher->getChecksToRunBefore());
    }

    public function testRequireAfterCheckIsCorrectlyRegistered(): void
    {
        $check = $this->createMock(SimpleCheckInterface::class);
        $check->method('getName')->willReturn('Some Check');
        $check->method('getPosition')->willReturn(SimpleCheckInterface::CHECK_AFTER);

        $exerciseDispatcher = new ExerciseDispatcher(
            $this->createMock(RunnerManager::class),
            new ResultAggregator(),
            new EventDispatcher(new ResultAggregator()),
            new CheckRepository([$check]),
        );

        $exerciseDispatcher->requireCheck(get_class($check));
        $this->assertEquals([$check], $exerciseDispatcher->getChecksToRunAfter());
    }

    public function testRequireCheckThrowsExceptionIfCheckIsNotSimpleOrListenable(): void
    {
        $check = $this->createMock(CheckInterface::class);
        $check->method('getName')->willReturn('Some Check');

        $exerciseDispatcher = new ExerciseDispatcher(
            $this->createMock(RunnerManager::class),
            new ResultAggregator(),
            new EventDispatcher(new ResultAggregator()),
            new CheckRepository([$check]),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Check: "%s" is not a listenable check', get_class($check)));
        $exerciseDispatcher->requireCheck(get_class($check));
    }

    public function testRequireListenableCheckAttachesToDispatcher(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $check = $this->createMock(ListenableCheckInterface::class);
        $check->expects($this->once())->method('attach')->with($eventDispatcher);

        $exerciseDispatcher = new ExerciseDispatcher(
            $this->createMock(RunnerManager::class),
            new ResultAggregator(),
            $eventDispatcher,
            new CheckRepository([$check]),
        );

        $exerciseDispatcher->requireCheck(get_class($check));
    }

    public function testVerifyThrowsExceptionIfCheckDoesNotSupportExerciseType(): void
    {
        $exercise = new CliExerciseImpl('Some Exercise');

        $check = $this->createMock(SimpleCheckInterface::class);
        $check->method('getName')->willReturn('Some Check');
        $check->method('getPosition')->willReturn(SimpleCheckInterface::CHECK_AFTER);
        $check->method('canRun')->with($exercise->getType())->willReturn(false);

        $runner = $this->createMock(ExerciseRunnerInterface::class);
        $runner->method('getRequiredChecks')->willReturn([get_class($check)]);

        $runnerManager = $this->createMock(RunnerManager::class);
        $runnerManager->method('getRunner')->with($exercise)->willReturn($runner);

        $exerciseDispatcher = new ExerciseDispatcher(
            $runnerManager,
            new ResultAggregator(),
            new EventDispatcher(new ResultAggregator()),
            new CheckRepository([$check]),
        );

        $this->expectException(CheckNotApplicableException::class);
        $this->expectExceptionMessage('Check: "Some Check" cannot process exercise: "Some Exercise" with type: "CLI"');

        $exerciseDispatcher->verify($exercise, new Input('app', ['program' => $this->file]));
    }

    public function testVerifyThrowsExceptionIfExerciseDoesNotImplementCorrectInterface(): void
    {
        $exercise = new CliExerciseImpl('Some Exercise');

        $check = $this->createMock(SimpleCheckInterface::class);
        $check->method('canRun')->with($exercise->getType())->willReturn(true);
        $check->method('getPosition')->willReturn(SimpleCheckInterface::CHECK_BEFORE);
        $check->method('getExerciseInterface')->willReturn('LolIDoNotExist');

        $runner = $this->createMock(ExerciseRunnerInterface::class);
        $runner->method('getRequiredChecks')->willReturn([get_class($check)]);

        $runnerManager = $this->createMock(RunnerManager::class);
        $runnerManager->method('getRunner')->with($exercise)->willReturn($runner);

        $exerciseDispatcher = new ExerciseDispatcher(
            $runnerManager,
            new ResultAggregator(),
            new EventDispatcher(new ResultAggregator()),
            new CheckRepository([$check]),
        );

        $this->expectException(ExerciseNotConfiguredException::class);
        $this->expectExceptionMessage('Exercise: "Some Exercise" should implement interface: "LolIDoNotExist"');

        $exerciseDispatcher->verify($exercise, new Input('app', ['program' => $this->file]));
    }

    public function testVerify(): void
    {
        $input = new Input('app', ['program' => $this->file]);
        $exercise = new CliExerciseImpl('Some Exercise');

        $check = $this->createMock(SimpleCheckInterface::class);
        $check->method('canRun')->with($exercise->getType())->willReturn(true);
        $check->method('getPosition')->willReturn(SimpleCheckInterface::CHECK_BEFORE);
        $check->method('getExerciseInterface')->willReturn(ExerciseInterface::class);
        $check->method('check')
            ->with($this->isInstanceOf(ExecutionContext::class))
            ->willReturn(new Success('Success!'));

        $runner = $this->createMock(ExerciseRunnerInterface::class);
        $runner->method('getRequiredChecks')->willReturn([get_class($check)]);
        $runner->method('verify')->willReturn(new Success('Success!'));

        $runnerManager = $this->createMock(RunnerManager::class);
        $runnerManager->method('getRunner')->with($exercise)->willReturn($runner);

        $exerciseDispatcher = new ExerciseDispatcher(
            $runnerManager,
            new ResultAggregator(),
            new EventDispatcher(new ResultAggregator()),
            new CheckRepository([$check]),
        );

        $result = $exerciseDispatcher->verify($exercise, $input);
        $this->assertTrue($result->isSuccessful());
    }

    public function testVerifyOnlyRunsRequiredChecks(): void
    {
        $input = new Input('app', ['program' => $this->file]);
        $exercise = new CliExerciseImpl('Some Exercise');

        $check1 = $this
            ->getMockBuilder(SimpleCheckInterface::class)
            ->setMockClassName('SimpleCheckMock1')
            ->getMock();

        $check1
            ->method('canRun')
            ->willReturn(true);

        $check1
            ->method('getPosition')
            ->willReturn(SimpleCheckInterface::CHECK_BEFORE);

        $check1
            ->method('getExerciseInterface')
            ->willReturn(ExerciseInterface::class);

        $check1
            ->method('check')
            ->with($this->isInstanceOf(ExecutionContext::class))
            ->willReturn(new Success('Success!'));

        $check2 = $this
            ->getMockBuilder(SimpleCheckInterface::class)
            ->setMockClassName('SimpleCheckMock2')
            ->getMock();

        $check2
            ->expects($this->never())
            ->method('check')
            ->with($this->isInstanceOf(ExecutionContext::class));

        $runner = $this->createMock(ExerciseRunnerInterface::class);
        $runner
            ->expects($this->once())
            ->method('getRequiredChecks')
            ->willReturn([get_class($check1)]);

        $runner
            ->method('verify')
            ->willReturn(new Success('Success!'));

        $runnerManager = $this->createMock(RunnerManager::class);
        $runnerManager
            ->method('getRunner')
            ->with($exercise)
            ->willReturn($runner);

        $exerciseDispatcher = new ExerciseDispatcher(
            $runnerManager,
            new ResultAggregator(),
            new EventDispatcher(new ResultAggregator()),
            new CheckRepository([$check1, $check2]),
        );

        $result = $exerciseDispatcher->verify($exercise, $input);
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertTrue($result->isSuccessful());
    }

    public function testVerifyWithBeforeAndAfterRequiredChecks(): void
    {
        $input = new Input('app', ['program' => $this->file]);
        $exercise = new CliExerciseImpl('Some Exercise');

        $check1 = $this->createMock(SimpleCheckInterface::class);
        $check1->method('canRun')->with($exercise->getType())->willReturn(true);
        $check1->method('getPosition')->willReturn(SimpleCheckInterface::CHECK_BEFORE);
        $check1->method('getExerciseInterface')->willReturn(ExerciseInterface::class);
        $check1->method('check')
            ->with($this->isInstanceOf(ExecutionContext::class))
            ->willReturn(new Success('Success!'));

        $check2 = $this->createMock(SimpleCheckInterface::class);
        $check2->method('canRun')->with($exercise->getType())->willReturn(true);
        $check2->method('getPosition')->willReturn(SimpleCheckInterface::CHECK_AFTER);
        $check2->method('getExerciseInterface')->willReturn(ExerciseInterface::class);
        $check2->method('check')
            ->with($this->isInstanceOf(ExecutionContext::class))
            ->willReturn(new Success('Success!'));

        $runner = $this->createMock(ExerciseRunnerInterface::class);
        $runner->method('getRequiredChecks')->willReturn([get_class($check1), get_class($check2)]);
        $runner->method('verify')->willReturn(new Success('Success!'));

        $runnerManager = $this->createMock(RunnerManager::class);
        $runnerManager->method('getRunner')->with($exercise)->willReturn($runner);

        $exerciseDispatcher = new ExerciseDispatcher(
            $runnerManager,
            new ResultAggregator(),
            new EventDispatcher(new ResultAggregator()),
            new CheckRepository([$check1, $check2]),
        );

        $result = $exerciseDispatcher->verify($exercise, $input);
        $this->assertTrue($result->isSuccessful());
        $this->assertCount(3, $result);
    }


    public function testWhenBeforeChecksFailTheyReturnImmediately(): void
    {
        $input = new Input('app', ['program' => $this->file]);
        $exercise = new CliExerciseImpl('Some Exercise');

        $check1 = $this
            ->getMockBuilder(SimpleCheckInterface::class)
            ->setMockClassName('SimpleCheckMock1')
            ->getMock();

        $check1
            ->method('canRun')
            ->willReturn(true);

        $check1
            ->method('getPosition')
            ->willReturn(SimpleCheckInterface::CHECK_BEFORE);

        $check1
            ->method('getExerciseInterface')
            ->willReturn(ExerciseInterface::class);

        $check1
            ->method('check')
            ->with($this->isInstanceOf(ExecutionContext::class))
            ->willReturn(new Failure('Failure', 'nope'));

        $check2 = $this
            ->getMockBuilder(SimpleCheckInterface::class)
            ->setMockClassName('SimpleCheckMock2')
            ->getMock();

        $check2
            ->method('canRun')
            ->willReturn(true);

        $check2
            ->method('getPosition')
            ->willReturn(SimpleCheckInterface::CHECK_BEFORE);

        $check2
            ->method('getExerciseInterface')
            ->willReturn(ExerciseInterface::class);

        $check2
            ->expects($this->never())
            ->method('check')
            ->with($this->isInstanceOf(ExecutionContext::class));

        $runner = $this->createMock(ExerciseRunnerInterface::class);
        $runner
            ->expects($this->once())
            ->method('getRequiredChecks')
            ->willReturn([get_class($check1), get_class($check2)]);

        $runner
            ->expects($this->never())
            ->method('verify')
            ->with($input);

        $runnerManager = $this->createMock(RunnerManager::class);
        $runnerManager->method('getRunner')->with($exercise)->willReturn($runner);

        $exerciseDispatcher = new ExerciseDispatcher(
            $runnerManager,
            new ResultAggregator(),
            new EventDispatcher(new ResultAggregator()),
            new CheckRepository([$check1, $check2]),
        );

        $result = $exerciseDispatcher->verify($exercise, $input);
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertFalse($result->isSuccessful());
    }

    public function testAllEventsAreDispatched(): void
    {
        $input = new Input('app', ['program' => $this->file]);
        $exercise = new CliExerciseImpl('Some Exercise');

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher
            ->expects($this->exactly(5))
            ->method('dispatch')
            ->withConsecutive(
                [
                    $this->callback(function ($event) {
                        return $event instanceof EventInterface && $event->getName() === 'verify.start';
                    })
                ],
                [
                    $this->callback(function ($event) {
                        return $event instanceof EventInterface && $event->getName() === 'verify.pre.execute';
                    })
                ],
                [
                    $this->callback(function ($event) {
                        return $event instanceof EventInterface && $event->getName() === 'verify.post.execute';
                    })
                ],
                [
                    $this->callback(function ($event) {
                        return $event instanceof EventInterface && $event->getName() === 'verify.post.check';
                    })
                ],
                [
                    $this->callback(function ($event) {
                        return $event instanceof EventInterface && $event->getName() === 'verify.finish';
                    })
                ]
            );

        $runner = $this->createMock(ExerciseRunnerInterface::class);
        $runner->method('getRequiredChecks')->willReturn([]);
        $runner->method('verify')->willReturn(new Success('Success!'));

        $runnerManager = $this->createMock(RunnerManager::class);
        $runnerManager->method('getRunner')->with($exercise)->willReturn($runner);

        $exerciseDispatcher = new ExerciseDispatcher(
            $runnerManager,
            new ResultAggregator(),
            $eventDispatcher,
            new CheckRepository(),
        );

        $exerciseDispatcher->verify($exercise, $input);
    }

    public function testVerifyPostExecuteIsStillDispatchedEvenIfRunnerThrowsException(): void
    {
        $input = new Input('app', ['program' => $this->file]);
        $exercise = new CliExerciseImpl('Some Exercise');

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->withConsecutive(
                [
                    $this->callback(function ($event) {
                        return $event instanceof EventInterface && $event->getName() === 'verify.start';
                    })
                ],
                [
                    $this->callback(function ($event) {
                        return $event instanceof EventInterface && $event->getName() === 'verify.pre.execute';
                    })
                ],
                [
                    $this->callback(function ($event) {
                        return $event instanceof EventInterface && $event->getName() === 'verify.post.execute';
                    })
                ]
            );

        $runner = $this->createMock(ExerciseRunnerInterface::class);
        $runner->method('getRequiredChecks')->willReturn([]);
        $runner->method('verify')->will($this->throwException(new RuntimeException()));

        $runnerManager = $this->createMock(RunnerManager::class);
        $runnerManager->method('getRunner')->with($exercise)->willReturn($runner);

        $exerciseDispatcher = new ExerciseDispatcher(
            $runnerManager,
            new ResultAggregator(),
            $eventDispatcher,
            new CheckRepository(),
        );

        $this->expectException(RuntimeException::class);
        $exerciseDispatcher->verify($exercise, $input);
    }

    public function testRun(): void
    {
        $input    = new Input('app', ['program' => $this->file]);
        $output   = $this->createMock(OutputInterface::class);
        $exercise = new CliExerciseImpl('Some Exercise');

        $runner = $this->createMock(ExerciseRunnerInterface::class);
        $runner->method('getRequiredChecks')->willReturn([]);
        $runner->method('run')->willReturn(true);

        $runnerManager = $this->createMock(RunnerManager::class);
        $runnerManager->method('getRunner')->with($exercise)->willReturn($runner);

        $exerciseDispatcher = new ExerciseDispatcher(
            $runnerManager,
            new ResultAggregator(),
            new EventDispatcher(new ResultAggregator()),
            new CheckRepository([new PhpLintCheck()]),
        );

        $this->assertTrue($exerciseDispatcher->run($exercise, $input, $output));
    }

    public function tearDown(): void
    {
        $this->filesystem->remove(dirname($this->file));
    }
}
