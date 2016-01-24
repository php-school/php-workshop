<?php

namespace PhpSchool\PhpWorkshopTest;

use Interop\Container\ContainerInterface;
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
        $this->check = $this->getMock(SimpleCheckInterface::class);
        $this->check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));

        $this->checkRepository = new CheckRepository([$this->check]);
        $this->runner = $this->getMock(ExerciseRunnerInterface::class);
        $this->runnerFactory = $this->getMock(RunnerFactory::class);
        $this->results = new ResultAggregator;
        $this->eventDispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

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
        $this->exercise = $this->getMock(ExerciseInterface::class);
        $this->solution = $this->getMock(SolutionInterface::class);

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
        $this->setExpectedException(InvalidArgumentException::class, 'Check: "NotACheck" does not exist');
        $this->exerciseDispatcher->requireCheck('NotACheck', ExerciseDispatcher::CHECK_BEFORE);
    }

    public function testRequireCheckThrowsExceptionIfPositionNotValid()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Parameter: "position" can only be one of: "before", "after" Received: "middle"'
        );
        $this->exerciseDispatcher->requireCheck(get_class($this->check), 'middle');
    }

    public function testRequireCheck()
    {
        $this->exerciseDispatcher->requireCheck(get_class($this->check), ExerciseDispatcher::CHECK_BEFORE);
        $this->exerciseDispatcher->requireCheck(get_class($this->check), ExerciseDispatcher::CHECK_AFTER);

        $checksToRunBefore = $this->readAttribute($this->exerciseDispatcher, 'checksToRunBefore');
        $checksToRunAfter  = $this->readAttribute($this->exerciseDispatcher, 'checksToRunAfter');

        $this->assertEquals([$this->check], $checksToRunBefore);
        $this->assertEquals([$this->check], $checksToRunAfter);
    }

    public function testRequireListenableCheckThrowsExceptionIfCheckDoesNotExist()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Check: "NotACheck" does not exist');
        $this->exerciseDispatcher->requireListenableCheck('NotACheck', ExerciseDispatcher::CHECK_BEFORE);
    }

    public function testRequireListenableCheckThrowsExceptionIfCheckIsNotCorrectType()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            sprintf('Check: "%s" is not a listenable check', get_class($this->check))
        );
        $this->exerciseDispatcher->requireListenableCheck(get_class($this->check));
    }

    public function testRequireListenableCheckAttachesToDispatcher()
    {
        $check = $this->getMock(ListenableCheckInterface::class);
        $this->checkRepository->registerCheck($check);

        $check
            ->expects($this->once())
            ->method('attach')
            ->with($this->eventDispatcher);

        $this->exerciseDispatcher->requireListenableCheck(get_class($check));
    }

    public function testVerifyThrowsExceptionIfCheckDoesNotSupportExerciseType()
    {
        $this->createExercise();
        $this->exerciseDispatcher->requireCheck(get_class($this->check), ExerciseDispatcher::CHECK_BEFORE);
        $this->check
            ->expects($this->once())
            ->method('canRun')
            ->with($this->exerciseType)
            ->will($this->returnValue(false));

        $msg  = 'Check: "Some Check" cannot process exercise: "Some Exercise" with ';
        $msg .= 'type: "PhpSchool\PhpWorkshop\ExerciseRunner\CliRunner"';
        $this->setExpectedException(CheckNotApplicableException::class, $msg);

        $this->exerciseDispatcher->verify($this->exercise, '');
    }

    public function testVerifyThrowsExceptionIfExerciseDoesNotImplementCorrectInterface()
    {
        $this->createExercise();
        $this->exerciseDispatcher->requireCheck(get_class($this->check), ExerciseDispatcher::CHECK_BEFORE);
        $this->check
            ->expects($this->once())
            ->method('canRun')
            ->with($this->exerciseType)
            ->will($this->returnValue(true));

        $this->check
            ->expects($this->once())
            ->method('getExerciseInterface')
            ->will($this->returnValue('LolIDoNotExist'));

        $this->setExpectedException(
            ExerciseNotConfiguredException::class,
            'Exercise: "Some Exercise" should implement interface: "LolIDoNotExist"'
        );

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
            ->will($this->returnValue($this->getMock(SuccessInterface::class)));

        $this->exerciseDispatcher->requireCheck(get_class($this->check), ExerciseDispatcher::CHECK_BEFORE);

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

        $doNotRunMe = $this->getMock(SimpleCheckInterface::class, [], [], 'DoNotRunMeCheck');
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
            ->will($this->returnValue($this->getMock(SuccessInterface::class)));

        $this->checkRepository->registerCheck($doNotRunMe);

        $this->exerciseDispatcher->requireCheck(get_class($this->check), ExerciseDispatcher::CHECK_BEFORE);
        $this->exerciseDispatcher->requireCheck(get_class($this->check), ExerciseDispatcher::CHECK_AFTER);

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

        $doNotRunMe = $this->getMock(SimpleCheckInterface::class, [], [], 'DoNotRunMeCheck');
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

        $this->exerciseDispatcher->requireCheck(get_class($this->check), ExerciseDispatcher::CHECK_BEFORE);
        $this->exerciseDispatcher->requireCheck(get_class($doNotRunMe), ExerciseDispatcher::CHECK_BEFORE);

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

        $this->setExpectedException(RuntimeException::class);
        $this->exerciseDispatcher->verify($this->exercise, $this->file);
    }

    public function testRun()
    {
        $exercise   = $this->getMock(ExerciseInterface::class);
        $output     = $this->getMock(OutputInterface::class);

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
