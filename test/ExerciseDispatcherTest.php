<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Check\CheckRepository;
use PhpSchool\PhpWorkshop\Check\ListenableCheckInterface;
use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Event\EventInterface;
use PhpSchool\PhpWorkshop\Exception\CheckNotApplicableException;
use PhpSchool\PhpWorkshop\Exception\ExerciseNotConfiguredException;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\ExerciseRunner\ExerciseRunnerInterface;
use PhpSchool\PhpWorkshop\ExerciseRunner\RunnerManager;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ExerciseDispatcherTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseDispatcherTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $file;

    public function setUp() : void
    {
        $this->filesystem = new Filesystem;
        $this->file = sprintf('%s/%s/submission.php', str_replace('\\', '/', sys_get_temp_dir()), $this->getName());
        mkdir(dirname($this->file), 0775, true);
        touch($this->file);
    }

    public function testGetEventDispatcher() : void
    {
        $eventDispatcher = new EventDispatcher($results = new ResultAggregator);

        $exerciseDispatcher = new ExerciseDispatcher(
            $this->prophesize(RunnerManager::class)->reveal(),
            $results,
            $eventDispatcher,
            new CheckRepository
        );

        $this->assertSame($eventDispatcher, $exerciseDispatcher->getEventDispatcher());
    }

    public function testRequireCheckThrowsExceptionIfCheckDoesNotExist() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Check: "NotACheck" does not exist');

        $exerciseDispatcher = new ExerciseDispatcher(
            $this->prophesize(RunnerManager::class)->reveal(),
            new ResultAggregator,
            $this->prophesize(EventDispatcher::class)->reveal(),
            new CheckRepository
        );
        $exerciseDispatcher->requireCheck('NotACheck');
    }

    public function testRequireCheckThrowsExceptionIfPositionNotValid() : void
    {
        $checkProphecy = $this->prophesize(SimpleCheckInterface::class);
        $checkProphecy->getName()->willReturn('Some Check');
        $checkProphecy->getPosition()->willReturn('middle');

        $check = $checkProphecy->reveal();

        $exerciseDispatcher = new ExerciseDispatcher(
            $this->prophesize(RunnerManager::class)->reveal(),
            new ResultAggregator,
            $this->prophesize(EventDispatcher::class)->reveal(),
            new CheckRepository([$check])
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter: "position" can only be one of: "before", "after" Received: "middle"');
        $exerciseDispatcher->requireCheck(get_class($check));
    }

    public function testRequireBeforeCheckIsCorrectlyRegistered() : void
    {
        $checkProphecy = $this->prophesize(SimpleCheckInterface::class);
        $checkProphecy->getName()->willReturn('Some Check');
        $checkProphecy->getPosition()->willReturn(SimpleCheckInterface::CHECK_BEFORE);

        $check = $checkProphecy->reveal();

        $exerciseDispatcher = new ExerciseDispatcher(
            $this->prophesize(RunnerManager::class)->reveal(),
            new ResultAggregator,
            $this->prophesize(EventDispatcher::class)->reveal(),
            new CheckRepository([$check])
        );

        $exerciseDispatcher->requireCheck(get_class($check));
        $checksToRunBefore = $this->readAttribute($exerciseDispatcher, 'checksToRunBefore');
        $this->assertEquals([$check], $checksToRunBefore);
    }

    public function testRequireAfterCheckIsCorrectlyRegistered() : void
    {
        $checkProphecy = $this->prophesize(SimpleCheckInterface::class);
        $checkProphecy->getName()->willReturn('Some Check');
        $checkProphecy->getPosition()->willReturn(SimpleCheckInterface::CHECK_AFTER);

        $check = $checkProphecy->reveal();

        $exerciseDispatcher = new ExerciseDispatcher(
            $this->prophesize(RunnerManager::class)->reveal(),
            new ResultAggregator,
            $this->prophesize(EventDispatcher::class)->reveal(),
            new CheckRepository([$check])
        );

        $exerciseDispatcher->requireCheck(get_class($check));
        $checksToRunBefore = $this->readAttribute($exerciseDispatcher, 'checksToRunAfter');
        $this->assertEquals([$check], $checksToRunBefore);
    }

    public function testRequireCheckThrowsExceptionIfCheckIsNotSimpleOrListenable() : void
    {
        $checkProphecy = $this->prophesize(CheckInterface::class);
        $checkProphecy->getName()->willReturn('Some Check');

        $check = $checkProphecy->reveal();

        $exerciseDispatcher = new ExerciseDispatcher(
            $this->prophesize(RunnerManager::class)->reveal(),
            new ResultAggregator,
            $this->prophesize(EventDispatcher::class)->reveal(),
            new CheckRepository([$check])
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Check: "%s" is not a listenable check', get_class($check)));
        $exerciseDispatcher->requireCheck(get_class($check));
    }

    public function testRequireListenableCheckAttachesToDispatcher() : void
    {
        $eventDispatcher = $this->prophesize(EventDispatcher::class)->reveal();
        $checkProphecy = $this->prophesize(ListenableCheckInterface::class);
        $checkProphecy->attach($eventDispatcher)->shouldBeCalled();
        $check = $checkProphecy->reveal();

        $exerciseDispatcher = new ExerciseDispatcher(
            $this->prophesize(RunnerManager::class)->reveal(),
            new ResultAggregator,
            $eventDispatcher,
            new CheckRepository([$check])
        );

        $exerciseDispatcher->requireCheck(get_class($check));
    }

    public function testVerifyThrowsExceptionIfCheckDoesNotSupportExerciseType() : void
    {
        $exercise = new CliExerciseImpl('Some Exercise');

        $checkProphecy = $this->prophesize(SimpleCheckInterface::class);
        $checkProphecy->canRun($exercise->getType())->willReturn(false);
        $checkProphecy->getPosition()->willReturn(SimpleCheckInterface::CHECK_BEFORE);
        $checkProphecy->getName()->willReturn('Some Check');

        $check = $checkProphecy->reveal();

        $runner = $this->prophesize(ExerciseRunnerInterface::class);
        $runner->getRequiredChecks()->willReturn([get_class($check)]);
        $runnerManager = $this->prophesize(RunnerManager::class);
        $runnerManager->getRunner($exercise)->willReturn($runner->reveal());

        $exerciseDispatcher = new ExerciseDispatcher(
            $runnerManager->reveal(),
            new ResultAggregator,
            $this->prophesize(EventDispatcher::class)->reveal(),
            new CheckRepository([$check])
        );

        $msg  = 'Check: "Some Check" cannot process exercise: "Some Exercise" with type: "CLI"';
        $this->expectException(CheckNotApplicableException::class);
        $this->expectExceptionMessage($msg);

        $exerciseDispatcher->verify($exercise, new Input('app'));
    }

    public function testVerifyThrowsExceptionIfExerciseDoesNotImplementCorrectInterface() : void
    {
        $exercise = new CliExerciseImpl('Some Exercise');

        $checkProphecy = $this->prophesize(SimpleCheckInterface::class);
        $checkProphecy->canRun($exercise->getType())->willReturn(true);
        $checkProphecy->getPosition()->willReturn(SimpleCheckInterface::CHECK_BEFORE);
        $checkProphecy->getExerciseInterface()->willReturn('LolIDoNotExist');

        $check = $checkProphecy->reveal();

        $runner = $this->prophesize(ExerciseRunnerInterface::class);
        $runner->getRequiredChecks()->willReturn([get_class($check)]);
        $runnerManager = $this->prophesize(RunnerManager::class);
        $runnerManager->getRunner($exercise)->willReturn($runner->reveal());

        $exerciseDispatcher = new ExerciseDispatcher(
            $runnerManager->reveal(),
            new ResultAggregator,
            $this->prophesize(EventDispatcher::class)->reveal(),
            new CheckRepository([$check])
        );

        $msg  = 'Exercise: "Some Exercise" should implement interface: "LolIDoNotExist"';
        $this->expectException(ExerciseNotConfiguredException::class);
        $this->expectExceptionMessage($msg);

        $exerciseDispatcher->verify($exercise, new Input('app'));
    }

    public function testVerify() : void
    {
        $input = new Input('app', ['program' => $this->file]);
        $exercise = new CliExerciseImpl('Some Exercise');

        $checkProphecy = $this->prophesize(SimpleCheckInterface::class);
        $checkProphecy->canRun($exercise->getType())->willReturn(true);
        $checkProphecy->getPosition()->willReturn(SimpleCheckInterface::CHECK_BEFORE);
        $checkProphecy->getExerciseInterface()->willReturn(ExerciseInterface::class);
        $checkProphecy->check($exercise, $input)->willReturn(new Success('Success!'));

        $check = $checkProphecy->reveal();

        $runner = $this->prophesize(ExerciseRunnerInterface::class);
        $runner->getRequiredChecks()->willReturn([get_class($check)]);
        $runner->verify($input)->willReturn(new Success('Success!'));
        $runnerManager = $this->prophesize(RunnerManager::class);
        $runnerManager->getRunner($exercise)->willReturn($runner->reveal());

        $exerciseDispatcher = new ExerciseDispatcher(
            $runnerManager->reveal(),
            new ResultAggregator,
            $this->prophesize(EventDispatcher::class)->reveal(),
            new CheckRepository([$check])
        );

        $result = $exerciseDispatcher->verify($exercise, $input);
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertTrue($result->isSuccessful());
    }

    public function testVerifyOnlyRunsRequiredChecks() : void
    {
        $input = new Input('app', ['program' => $this->file]);
        $exercise = new CliExerciseImpl('Some Exercise');

        $checkProphecy1 = $this->prophesize(SimpleCheckInterface::class);
        $checkProphecy1->canRun($exercise->getType())->willReturn(true);
        $checkProphecy1->getPosition()->willReturn(SimpleCheckInterface::CHECK_BEFORE);
        $checkProphecy1->getExerciseInterface()->willReturn(ExerciseInterface::class);
        $checkProphecy1->check($exercise, $input)->willReturn(new Success('Success!'));

        $checkProphecy2 = $this->prophesize(SimpleCheckInterface::class);
        $checkProphecy2->check($exercise, $input)->shouldNotBeCalled();

        $check1 = $checkProphecy1->reveal();
        $check2 = $checkProphecy2->reveal();

        $runner = $this->prophesize(ExerciseRunnerInterface::class);
        $runner->getRequiredChecks()->willReturn([get_class($check1)]);
        $runner->verify($input)->willReturn(new Success('Success!'));
        $runnerManager = $this->prophesize(RunnerManager::class);
        $runnerManager->getRunner($exercise)->willReturn($runner->reveal());

        $exerciseDispatcher = new ExerciseDispatcher(
            $runnerManager->reveal(),
            new ResultAggregator,
            $this->prophesize(EventDispatcher::class)->reveal(),
            new CheckRepository([$check1, $check2])
        );

        $result = $exerciseDispatcher->verify($exercise, $input);
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertTrue($result->isSuccessful());
    }

    public function testVerifyWithBeforeAndAfterRequiredChecks() : void
    {
        $input = new Input('app', ['program' => $this->file]);
        $exercise = new CliExerciseImpl('Some Exercise');

        $checkProphecy1 = $this->prophesize(SimpleCheckInterface::class);
        $checkProphecy1->canRun($exercise->getType())->willReturn(true);
        $checkProphecy1->getPosition()->willReturn(SimpleCheckInterface::CHECK_BEFORE);
        $checkProphecy1->getExerciseInterface()->willReturn(ExerciseInterface::class);
        $checkProphecy1->check($exercise, $input)->willReturn(new Success('Success!'));

        $checkProphecy2 = $this->prophesize(SimpleCheckInterface::class);
        $checkProphecy2->canRun($exercise->getType())->willReturn(true);
        $checkProphecy2->getPosition()->willReturn(SimpleCheckInterface::CHECK_AFTER);
        $checkProphecy2->getExerciseInterface()->willReturn(ExerciseInterface::class);
        $checkProphecy2->check($exercise, $input)->willReturn(new Success('Success!'));

        $check1 = $checkProphecy1->reveal();
        $check2 = $checkProphecy2->reveal();

        $runner = $this->prophesize(ExerciseRunnerInterface::class);
        $runner->getRequiredChecks()->willReturn([get_class($check1), get_class($check2)]);
        $runner->verify($input)->willReturn(new Success('Success!'));
        $runnerManager = $this->prophesize(RunnerManager::class);
        $runnerManager->getRunner($exercise)->willReturn($runner->reveal());

        $exerciseDispatcher = new ExerciseDispatcher(
            $runnerManager->reveal(),
            new ResultAggregator,
            $this->prophesize(EventDispatcher::class)->reveal(),
            new CheckRepository([$check1, $check2])
        );

        $result = $exerciseDispatcher->verify($exercise, $input);
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertTrue($result->isSuccessful());
        $this->assertCount(3, $result);
    }


    public function testWhenBeforeChecksFailTheyReturnImmediately() : void
    {
        $input = new Input('app', ['program' => $this->file]);
        $exercise = new CliExerciseImpl('Some Exercise');

        $checkProphecy1 = $this->prophesize(SimpleCheckInterface::class);
        $checkProphecy1->canRun($exercise->getType())->willReturn(true);
        $checkProphecy1->getPosition()->willReturn(SimpleCheckInterface::CHECK_BEFORE);
        $checkProphecy1->getExerciseInterface()->willReturn(ExerciseInterface::class);
        $checkProphecy1->check($exercise, $input)->willReturn(new Failure('Failure', 'nope'));

        $checkProphecy2 = $this->prophesize(SimpleCheckInterface::class);
        $checkProphecy2->canRun($exercise->getType())->willReturn(true);
        $checkProphecy2->getPosition()->willReturn(SimpleCheckInterface::CHECK_BEFORE);
        $checkProphecy2->getExerciseInterface()->willReturn(ExerciseInterface::class);
        $checkProphecy2->check($exercise, $input)->shouldNotBeCalled();

        $check1 = $checkProphecy1->reveal();
        $check2 = $checkProphecy2->reveal();

        $runner = $this->prophesize(ExerciseRunnerInterface::class);
        $runner->getRequiredChecks()->willReturn([get_class($check1), get_class($check2)]);
        $runner->verify($input)->shouldNotBeCalled();
        $runnerManager = $this->prophesize(RunnerManager::class);
        $runnerManager->getRunner($exercise)->willReturn($runner->reveal());

        $exerciseDispatcher = new ExerciseDispatcher(
            $runnerManager->reveal(),
            new ResultAggregator,
            $this->prophesize(EventDispatcher::class)->reveal(),
            new CheckRepository([$check1, $check2])
        );

        $result = $exerciseDispatcher->verify($exercise, $input);
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertFalse($result->isSuccessful());
    }

    public function testAllEventsAreDispatched() : void
    {
        $input = new Input('app', ['program' => $this->file]);
        $exercise = new CliExerciseImpl('Some Exercise');

        $eventDispatcher = $this->prophesize(EventDispatcher::class);

        $eventDispatcher
            ->dispatch(Argument::that(function ($event) {
                return $event instanceof EventInterface && $event->getName() === 'verify.start';
            }))
            ->shouldBeCalled();
        $eventDispatcher
            ->dispatch(Argument::that(function ($event) {
                return $event instanceof EventInterface && $event->getName() === 'verify.pre.execute';
            }))
            ->shouldBeCalled();
        $eventDispatcher
            ->dispatch(Argument::that(function ($event) {
                return $event instanceof EventInterface && $event->getName() === 'verify.post.execute';
            }))
            ->shouldBeCalled();
        $eventDispatcher
            ->dispatch(Argument::that(function ($event) {
                return $event instanceof EventInterface && $event->getName() === 'verify.post.check';
            }))
            ->shouldBeCalled();
        $eventDispatcher
            ->dispatch(Argument::that(function ($event) {
                return $event instanceof EventInterface && $event->getName() === 'verify.finish';
            }))
            ->shouldBeCalled();


        $runner = $this->prophesize(ExerciseRunnerInterface::class);
        $runner->getRequiredChecks()->willReturn([]);
        $runner->verify($input)->willReturn(new Success('Success!'));
        $runnerManager = $this->prophesize(RunnerManager::class);
        $runnerManager->getRunner($exercise)->willReturn($runner->reveal());

        $exerciseDispatcher = new ExerciseDispatcher(
            $runnerManager->reveal(),
            new ResultAggregator,
            $eventDispatcher->reveal(),
            new CheckRepository()
        );

        $exerciseDispatcher->verify($exercise, $input);
    }

    public function testVerifyPostExecuteIsStillDispatchedEvenIfRunnerThrowsException() : void
    {
        $input = new Input('app', ['program' => $this->file]);
        $exercise = new CliExerciseImpl('Some Exercise');

        $eventDispatcher = $this->prophesize(EventDispatcher::class);

        $eventDispatcher
            ->dispatch(Argument::that(function ($event) {
                return $event instanceof EventInterface && $event->getName() === 'verify.start';
            }))
            ->shouldBeCalled();
        $eventDispatcher
            ->dispatch(Argument::that(function ($event) {
                return $event instanceof EventInterface && $event->getName() === 'verify.pre.execute';
            }))
            ->shouldBeCalled();
        $eventDispatcher
            ->dispatch(Argument::that(function ($event) {
                return $event instanceof EventInterface && $event->getName() === 'verify.post.execute';
            }))
            ->shouldBeCalled();

        $runner = $this->prophesize(ExerciseRunnerInterface::class);
        $runner->getRequiredChecks()->willReturn([]);
        $runner->verify($input)->willThrow(new RuntimeException);
        $runnerManager = $this->prophesize(RunnerManager::class);
        $runnerManager->getRunner($exercise)->willReturn($runner->reveal());

        $exerciseDispatcher = new ExerciseDispatcher(
            $runnerManager->reveal(),
            new ResultAggregator,
            $eventDispatcher->reveal(),
            new CheckRepository()
        );

        $this->expectException(RuntimeException::class);
        $exerciseDispatcher->verify($exercise, $input);
    }

    public function testRun() : void
    {
        $input    = new Input('app', ['program' => $this->file]);
        $output   = $this->prophesize(OutputInterface::class)->reveal();
        $exercise = new CliExerciseImpl('Some Exercise');

        $runner = $this->prophesize(ExerciseRunnerInterface::class);
        $runner->getRequiredChecks()->willReturn([]);
        $runner->run($input, $output)->willReturn(true);
        $runnerManager = $this->prophesize(RunnerManager::class);
        $runnerManager->getRunner($exercise)->willReturn($runner->reveal());

        $exerciseDispatcher = new ExerciseDispatcher(
            $runnerManager->reveal(),
            new ResultAggregator,
            $this->prophesize(EventDispatcher::class)->reveal(),
            new CheckRepository()
        );

        $this->assertTrue($exerciseDispatcher->run($exercise, $input, $output));
    }

    public function tearDown()
    {
        $this->filesystem->remove(dirname($this->file));
    }
}
