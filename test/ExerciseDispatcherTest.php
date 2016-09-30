<?php

namespace PhpSchool\PhpWorkshopTest;

use Interop\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Check\CheckRepository;
use PhpSchool\PhpWorkshop\Check\ListenableCheckInterface;
use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\CheckNotApplicableException;
use PhpSchool\PhpWorkshop\Exception\ExerciseNotConfiguredException;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\ExerciseRunner\ExerciseRunnerInterface;
use PhpSchool\PhpWorkshop\Factory\RunnerFactory;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\Result\SuccessInterface;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
use PHPUnit_Framework_TestCase;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ExerciseDispatcherTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseDispatcherTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var SimpleCheckInterface
     */
    private $check;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var CheckRepository
     */
    private $checkRepository;

    /**
     * @var ExerciseDispatcher
     */
    private $exerciseDispatcher;

    /**
     * @var ExerciseRunnerInterface
     */
    private $runner;

    /**
     * @var RunnerFactory
     */
    private $runnerFactory;

    /**
     * @var string
     */
    private $file;

    /**
     * @var ExerciseInterface
     */
    private $exercise;

    /**
     * @var ExerciseType
     */
    private $exerciseType;

    /**
     * @var SolutionInterface
     */
    private $solution;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var ResultAggregator
     */
    private $results;

    public function setUp()
    {
        $this->filesystem = new Filesystem;
        $this->check = $this->createMock(SimpleCheckInterface::class);
        $this->check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));

        $this->check
            ->expects($this->any())
            ->method('getPosition')
            ->willReturn(SimpleCheckInterface::CHECK_BEFORE);

        $this->checkRepository = new CheckRepository([$this->check]);
        $this->runner = $this->createMock(ExerciseRunnerInterface::class);
        $this->runnerFactory = $this->createMock(RunnerFactory::class);
        $this->results = new ResultAggregator;
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);

        $this->exerciseDispatcher = new ExerciseDispatcher(
            $this->runnerFactory,
            $this->results,
            $this->eventDispatcher,
            $this->checkRepository
        );

        $this->assertSame($this->eventDispatcher, $this->exerciseDispatcher->getEventDispatcher());

        $this->file = sprintf('%s/%s/submission.php', str_replace('\\', '/', sys_get_temp_dir()), $this->getName());

        mkdir(dirname($this->file), 0775, true);
        touch($this->file);
    }

    private function createExercise()
    {
        $this->exercise = $this->createMock(ExerciseInterface::class);
        $this->solution = $this->createMock(SolutionInterface::class);

        $this->exercise
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Exercise'));

        $this->exerciseType = new ExerciseType(ExerciseType::CLI);

        $this->exercise
            ->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($this->exerciseType));
    }

    private function mockRunner(ExerciseInterface $exercise = null)
    {
        $this->runnerFactory
            ->expects($this->once())
            ->method('create')
            ->with($exercise ? $exercise : $this->exercise, $this->eventDispatcher)
            ->will($this->returnValue($this->runner));
    }

    public function testRequireCheckThrowsExceptionIfCheckDoesNotExist()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Check: "NotACheck" does not exist');
        $this->exerciseDispatcher->requireCheck('NotACheck');
    }

    public function testRequireCheckThrowsExceptionIfPositionNotValid()
    {
        $check = $this->createMock(SimpleCheckInterface::class);
        $check
            ->expects($this->any())
            ->method('getName')
            ->willReturn('Some Check');

        $check
            ->expects($this->any())
            ->method('getPosition')
            ->willReturn('middle');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter: "position" can only be one of: "before", "after" Received: "middle"');
        $this->checkRepository->registerCheck($check);
        $this->exerciseDispatcher->requireCheck(get_class($check));
    }

    public function testRequireBeforeCheck()
    {
        $this->exerciseDispatcher->requireCheck(get_class($this->check));
        $checksToRunBefore = $this->readAttribute($this->exerciseDispatcher, 'checksToRunBefore');
        $this->assertEquals([$this->check], $checksToRunBefore);
    }

    public function testRequireAfterCheck()
    {
        $check = $this->createMock(SimpleCheckInterface::class);
        $check
            ->expects($this->any())
            ->method('getName')
            ->willReturn('Some Check');

        $check
            ->expects($this->any())
            ->method('getPosition')
            ->willReturn(SimpleCheckInterface::CHECK_AFTER);

        $this->checkRepository->registerCheck($check);

        $this->exerciseDispatcher->requireCheck(get_class($this->check));
        $checksToRunAfter = $this->readAttribute($this->exerciseDispatcher, 'checksToRunAfter');
        $this->assertEquals([$check], $checksToRunAfter);
    }

    public function testRequireCheckThrowsExceptionIfCheckIsNotSimpleOrListenable()
    {
        $check = $this->createMock(CheckInterface::class);
        $check
            ->expects($this->any())
            ->method('getName')
            ->willReturn('Some Check');

        $this->checkRepository->registerCheck($check);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Check: "%s" is not a listenable check', get_class($check)));
        $this->exerciseDispatcher->requireCheck(get_class($check));
    }

    public function testRequireListenableCheckAttachesToDispatcher()
    {
        $check = $this->createMock(ListenableCheckInterface::class);
        $this->checkRepository->registerCheck($check);

        $check
            ->expects($this->once())
            ->method('attach')
            ->with($this->eventDispatcher);

        $this->exerciseDispatcher->requireCheck(get_class($check));
    }

    public function testVerifyThrowsExceptionIfCheckDoesNotSupportExerciseType()
    {
        $this->createExercise();
        $this->exerciseDispatcher->requireCheck(get_class($this->check));
        $this->check
            ->expects($this->once())
            ->method('canRun')
            ->with($this->exerciseType)
            ->will($this->returnValue(false));

        $msg  = 'Check: "Some Check" cannot process exercise: "Some Exercise" with type: "CLI"';
        $this->expectException(CheckNotApplicableException::class);
        $this->expectExceptionMessage($msg);

        $this->exerciseDispatcher->verify($this->exercise, '');
    }

    public function testVerifyThrowsExceptionIfExerciseDoesNotImplementCorrectInterface()
    {
        $this->createExercise();
        $this->exerciseDispatcher->requireCheck(get_class($this->check));
        $this->check
            ->expects($this->once())
            ->method('canRun')
            ->with($this->exerciseType)
            ->will($this->returnValue(true));

        $this->check
            ->expects($this->once())
            ->method('getExerciseInterface')
            ->will($this->returnValue('LolIDoNotExist'));

        $this->expectException(ExerciseNotConfiguredException::class);
        $this->expectExceptionMessage('Exercise: "Some Exercise" should implement interface: "LolIDoNotExist"');

        $this->exerciseDispatcher->verify($this->exercise, '');
    }

    public function testVerify()
    {
        $this->createExercise();
        $this->exercise
            ->expects($this->once())
            ->method('configure')
            ->with($this->exerciseDispatcher);

        $this->check
            ->expects($this->once())
            ->method('check')
            ->will($this->returnValue(new Success('Success')));

        $this->check
            ->expects($this->once())
            ->method('canRun')
            ->with($this->exerciseType)
            ->will($this->returnValue(true));

        $this->check
            ->expects($this->once())
            ->method('getExerciseInterface')
            ->will($this->returnValue(ExerciseInterface::class));

        $this->mockRunner();
        $this->runner
            ->expects($this->once())
            ->method('verify')
            ->with($this->file)
            ->will($this->returnValue($this->createMock(SuccessInterface::class)));

        $this->exerciseDispatcher->requireCheck(get_class($this->check));

        $result = $this->exerciseDispatcher->verify($this->exercise, $this->file);
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertTrue($result->isSuccessful());
    }

    public function testVerifyOnlyRunsRequiredChecks()
    {
        $this->createExercise();
        $this->check
            ->expects($this->exactly(2))
            ->method('check')
            ->will($this->returnValue(new Success('Success', 'nope')));

        $doNotRunMe = $this->getMockBuilder(SimpleCheckInterface::class)
            ->setMockClassName('DoNotRunMeCheck')
            ->getMock();

        $doNotRunMe
            ->expects($this->never())
            ->method('check');

        $this->check
            ->expects($this->exactly(2))
            ->method('canRun')
            ->with($this->exerciseType)
            ->will($this->returnValue(true));

        $this->check
            ->expects($this->exactly(2))
            ->method('getExerciseInterface')
            ->will($this->returnValue(ExerciseInterface::class));

        $this->mockRunner();
        $this->runner
            ->expects($this->once())
            ->method('verify')
            ->with($this->file)
            ->will($this->returnValue($this->createMock(SuccessInterface::class)));

        $this->checkRepository->registerCheck($doNotRunMe);

        $this->exerciseDispatcher->requireCheck(get_class($this->check));
        $this->exerciseDispatcher->requireCheck(get_class($this->check));

        $result = $this->exerciseDispatcher->verify($this->exercise, $this->file);
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertTrue($result->isSuccessful());
    }

    public function testWhenBeforeChecksFailTheyReturnImmediately()
    {
        $this->createExercise();
        $this->check
            ->expects($this->once())
            ->method('check')
            ->will($this->returnValue(new Failure('Failure', 'nope')));

        $doNotRunMe = $this->getMockBuilder(SimpleCheckInterface::class)
            ->setMockClassName('DoNotRunMeCheck')
            ->getMock();

        $doNotRunMe
            ->expects($this->once())
            ->method('getPosition')
            ->willReturn(SimpleCheckInterface::CHECK_BEFORE);
        $doNotRunMe
            ->expects($this->never())
            ->method('check');

        $this->check
            ->expects($this->once())
            ->method('canRun')
            ->with($this->exerciseType)
            ->will($this->returnValue(true));

        $this->check
            ->expects($this->once())
            ->method('getExerciseInterface')
            ->will($this->returnValue(ExerciseInterface::class));

        $doNotRunMe
            ->expects($this->once())
            ->method('canRun')
            ->with($this->exerciseType)
            ->will($this->returnValue(true));

        $doNotRunMe
            ->expects($this->once())
            ->method('getExerciseInterface')
            ->will($this->returnValue(ExerciseInterface::class));

        $this->checkRepository->registerCheck($doNotRunMe);

        $this->exerciseDispatcher->requireCheck(get_class($this->check));
        $this->exerciseDispatcher->requireCheck(get_class($doNotRunMe));

        $result = $this->exerciseDispatcher->verify($this->exercise, $this->file);
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertFalse($result->isSuccessful());
    }

    public function testAllEventsAreDispatched()
    {
        $this->eventDispatcher
            ->expects($this->exactly(5))
            ->method('dispatch')
            ->withConsecutive(
                [$this->isInstanceOf(Event::class)],
                [$this->isInstanceOf(Event::class)],
                [$this->isInstanceOf(Event::class)],
                [$this->isInstanceOf(Event::class)],
                [$this->isInstanceOf(Event::class)]
            );

        $this->createExercise();
        $this->mockRunner();
        $this->runner
            ->expects($this->once())
            ->method('verify')
            ->with($this->file)
            ->will($this->returnValue(new Success('test')));

        $this->exerciseDispatcher->verify($this->exercise, $this->file);
    }

    public function testVerifyPostExecuteIsStillDispatchedEvenIfRunnerThrowsException()
    {
        $this->eventDispatcher
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->withConsecutive(
                [$this->isInstanceOf(Event::class)],
                [$this->isInstanceOf(Event::class)],
                [$this->isInstanceOf(Event::class)]
            );

        $this->createExercise();
        $this->mockRunner();
        $this->runner
            ->expects($this->once())
            ->method('verify')
            ->with($this->file)
            ->will($this->throwException(new RuntimeException));

        $this->expectException(RuntimeException::class);
        $this->exerciseDispatcher->verify($this->exercise, $this->file);
    }

    public function testRun()
    {
        $exercise   = $this->createMock(ExerciseInterface::class);
        $output     = $this->createMock(OutputInterface::class);

        $this->mockRunner($exercise);
        $this->runner
            ->expects($this->once())
            ->method('run')
            ->with($this->file, $output)
            ->will($this->returnValue(true));

        $this->assertTrue($this->exerciseDispatcher->run($exercise, $this->file, $output));
    }

    public function tearDown()
    {
        $this->filesystem->remove(dirname($this->file));
    }
}
