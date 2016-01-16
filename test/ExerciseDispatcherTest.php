<?php

namespace PhpSchool\PhpWorkshopTest;

use Interop\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Check\CheckRepository;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\CodePatcher;
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
use PhpSchool\PhpWorkshopTest\Asset\SelfCheckExerciseInterface;
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
     * @var CheckInterface
     */
    private $check;

    /**
     * @var CodePatcher
     */
    private $codePatcher;

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
        $this->check = $this->getMock(CheckInterface::class);
        $this->check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));

        $this->codePatcher = $this->getMockBuilder(CodePatcher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkRepository = new CheckRepository([$this->check]);
        $this->runner = $this->getMock(ExerciseRunnerInterface::class);
        $this->runnerFactory = $this->getMock(RunnerFactory::class);
        $this->results = new ResultAggregator;
        $this->eventDispatcher = new EventDispatcher($this->results);
        $this->exerciseDispatcher = new ExerciseDispatcher(
            $this->runnerFactory,
            $this->results,
            $this->eventDispatcher,
            $this->checkRepository,
            $this->codePatcher
        );

        $this->file = sprintf('%s/%s/submission.php', str_replace('\\', '/', sys_get_temp_dir()), $this->getName());

        mkdir(dirname($this->file), 0775, true);
        touch($this->file);
    }

    private function createExercise()
    {
        $this->exercise = $this->getMock(ExerciseInterface::class);
        $this->solution = $this->getMock(SolutionInterface::class);

        $this->exerciseType = ExerciseType::CLI();
        $this->exercise
            ->expects($this->atLeastOnce())
            ->method('getType')
            ->will($this->returnValue($this->exerciseType));

        $this->exercise
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Exercise'));
    }

    private function mockRunner(ExerciseType $exerciseType = null)
    {
        $this->runnerFactory
            ->expects($this->once())
            ->method('create')
            ->with($exerciseType ? $exerciseType : $this->exerciseType, $this->eventDispatcher)
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
            ->with($this->exercise, $this->file)
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

        $doNotRunMe = $this->getMock(CheckInterface::class, [], [], 'DoNotRunMeCheck');
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
            ->with($this->exercise, $this->file)
            ->will($this->returnValue($this->getMock(SuccessInterface::class)));

        $this->checkRepository->registerCheck($doNotRunMe);

        $this->exerciseDispatcher->requireCheck(get_class($this->check), ExerciseDispatcher::CHECK_BEFORE);
        $this->exerciseDispatcher->requireCheck(get_class($this->check), ExerciseDispatcher::CHECK_AFTER);

        $result = $this->exerciseDispatcher->verify($this->exercise, $this->file);
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertTrue($result->isSuccessful());
    }

    public function testWhenBeforeChecksFailTheyReturnImmediatelyEarly()
    {
        $this->createExercise();
        $this->check
            ->expects($this->once())
            ->method('check')
            ->will($this->returnValue(new Failure('Failure', 'nope')));

        $doNotRunMe = $this->getMock(CheckInterface::class, [], [], 'DoNotRunMeCheck');
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

    public function testSelfCheck()
    {
        $this->check
            ->expects($this->once())
            ->method('check')
            ->will($this->returnValue(new Success($this->check->getName())));

        $exerciseType = ExerciseType::CLI();
        $this->check
            ->expects($this->once())
            ->method('canRun')
            ->with($exerciseType)
            ->will($this->returnValue(true));

        $this->check
            ->expects($this->once())
            ->method('getExerciseInterface')
            ->will($this->returnValue(ExerciseInterface::class));

        $exercise = $this->getMock(SelfCheckExerciseInterface::class);
        $exercise
            ->expects($this->atLeastOnce())
            ->method('getType')
            ->will($this->returnValue($exerciseType));

        $exercise
            ->expects($this->atLeastOnce())
            ->method('check')
            ->with($this->file)
            ->will($this->returnValue(new Success($this->check->getName())));

        $this->mockRunner($exerciseType);
        $this->runner
            ->expects($this->once())
            ->method('verify')
            ->with($exercise, $this->file)
            ->will($this->returnValue($this->getMock(SuccessInterface::class)));

        $this->exerciseDispatcher->requireCheck(get_class($this->check), ExerciseDispatcher::CHECK_BEFORE);

        $result = $this->exerciseDispatcher->verify($exercise, $this->file);
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertCount(3, $result);
    }

    public function testCodeWhichRequiresPatchingIsModifiedOnDiskAfterPreChecksAndThenReverted()
    {
        $this->createExercise();

        file_put_contents($this->file, 'ORIGINAL CONTENT');

        $this->exerciseDispatcher->requireCheck(get_class($this->check), ExerciseDispatcher::CHECK_AFTER);
        $this->check
            ->expects($this->once())
            ->method('check')
            ->with($this->exercise, $this->file)
            ->will($this->returnCallback(function (ExerciseInterface $exercise, $file) {
                $this->assertStringEqualsFile($file, 'MODIFIED CONTENT');
                return new Success('test');
            }));

        $this->check
            ->expects($this->once())
            ->method('canRun')
            ->with($this->exerciseType)
            ->will($this->returnValue(true));

        $this->check
            ->expects($this->once())
            ->method('getExerciseInterface')
            ->will($this->returnValue(ExerciseInterface::class));

        $this->codePatcher
            ->expects($this->once())
            ->method('patch')
            ->with($this->exercise, 'ORIGINAL CONTENT')
            ->will($this->returnValue('MODIFIED CONTENT'));

        $this->mockRunner();
        $this->runner
            ->expects($this->once())
            ->method('verify')
            ->with($this->exercise, $this->file)
            ->will($this->returnValue($this->getMock(SuccessInterface::class)));

        $this->exerciseDispatcher->verify($this->exercise, $this->file);
        $this->assertStringEqualsFile($this->file, 'ORIGINAL CONTENT');
    }

    public function testPatchedCodeIsRevertedIfExceptionIsThrownAnywhere()
    {
        $this->createExercise();

        file_put_contents($this->file, 'ORIGINAL CONTENT');

        $this->exerciseDispatcher->requireCheck(get_class($this->check), ExerciseDispatcher::CHECK_AFTER);
        $this->check
            ->expects($this->once())
            ->method('check')
            ->with($this->exercise, $this->file)
            ->will($this->throwException(new RuntimeException));

        $this->check
            ->expects($this->once())
            ->method('canRun')
            ->with($this->exerciseType)
            ->will($this->returnValue(true));

        $this->check
            ->expects($this->once())
            ->method('getExerciseInterface')
            ->will($this->returnValue(ExerciseInterface::class));

        $this->codePatcher
            ->expects($this->once())
            ->method('patch')
            ->with($this->exercise, 'ORIGINAL CONTENT')
            ->will($this->returnValue('MODIFIED CONTENT'));

        $this->mockRunner();
        $this->runner
            ->expects($this->once())
            ->method('verify')
            ->with($this->exercise, $this->file)
            ->will($this->returnValue($this->getMock(SuccessInterface::class)));

        try {
            $this->exerciseDispatcher->verify($this->exercise, $this->file);
        } catch (RuntimeException $e) {
        }
        $this->assertStringEqualsFile($this->file, 'ORIGINAL CONTENT');
    }

    public function testRun()
    {
        $exercise   = $this->getMock(ExerciseInterface::class);
        $output     = $this->getMock(OutputInterface::class);

        $exerciseType = ExerciseType::CLI();
        $exercise
            ->expects($this->atLeastOnce())
            ->method('getType')
            ->will($this->returnValue($exerciseType));

        $this->mockRunner($exerciseType);
        $this->runner
            ->expects($this->once())
            ->method('run')
            ->with($exercise, $this->file, $output)
            ->will($this->returnValue(true));

        $this->assertTrue($this->exerciseDispatcher->run($exercise, $this->file, $output));
    }

    public function tearDown()
    {
        $this->filesystem->remove(dirname($this->file));
    }
}
