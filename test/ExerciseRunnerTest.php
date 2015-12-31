<?php


namespace PhpSchool\PhpWorkshopTest;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\CodePatcher;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
use PhpSchool\PhpWorkshopTest\Asset\SelfCheckExercise;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\StdOutExerciseCheck;
use PhpSchool\PhpWorkshop\ExerciseRunner;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshopTest\Asset\StdOutExercise;
use RuntimeException;
use stdClass;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ExerciseRunnerTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseRunnerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CheckInterface
     */
    private $check;

    /**
     * @var ExerciseRunner
     */
    private $exerciseRunner;

    /**
     * @var CodePatcher
     */
    private $codePatcher;

    /**
     * @var string
     */
    private $file;

    /**
     * @var Filesystem
     */
    private $filesystem;

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
        $this->exerciseRunner = new ExerciseRunner($this->codePatcher);

        $this->file = sprintf('%s/%s/submission.php', str_replace('\\', '/', sys_get_temp_dir()), $this->getName());
        
        mkdir(dirname($this->file), 0775, true);
        touch($this->file);
    }
    
    public function testRegisterCheckExerciseWithNonStringNonNullThrowsException()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Expected: "string" Received: "stdClass"'
        );
        $this->exerciseRunner->registerCheck($this->getMock(CheckInterface::class), new stdClass);
    }

    public function testRegisterPreCheckExerciseWithNonStringNonNullThrowsException()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Expected: "string" Received: "stdClass"'
        );
        $this->exerciseRunner->registerPreCheck($this->getMock(CheckInterface::class), new stdClass);
    }

    public function testRegisterCheck()
    {
        $this->exerciseRunner->registerCheck($this->getMock(CheckInterface::class), 'SomeInterface');
    }

    public function testRegisterPreCheck()
    {
        $this->exerciseRunner->registerPreCheck($this->getMock(CheckInterface::class), 'SomeInterface');
    }

    public function testRunExerciseOnlyRunsRequiredChecksAndPreChecks()
    {
        $doNotRunMe = $this->getMock(CheckInterface::class);

        $doNotRunMe
            ->expects($this->never())
            ->method('check');

        $this->exerciseRunner->registerCheck($doNotRunMe, StdOutExerciseCheck::class);
        $this->exerciseRunner->registerPreCheck($doNotRunMe, StdOutExerciseCheck::class);
        
        $solution = $this->getMock(SolutionInterface::class);
        $exercise = $this->getMock(ExerciseInterface::class);
        $exercise->expects($this->any())
            ->method('getSolution')
            ->will($this->returnValue($solution));
        
        $result = $this->exerciseRunner->runExercise($exercise, $this->file);
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertTrue($result->isSuccessful());
    }

    public function testRunExerciseWithRequiredChecks()
    {
        $runMe = $this->getMock(CheckInterface::class);
        $this->exerciseRunner->registerCheck($runMe, StdOutExerciseCheck::class);
        
        $runMe
            ->expects($this->once())
            ->method('check')
            ->will($this->returnValue(new Success($this->check)));

        $solution = $this->getMock(SolutionInterface::class);
        $exercise = $this->getMock(StdOutExercise::class);
        $exercise->expects($this->any())
            ->method('getSolution')
            ->will($this->returnValue($solution));

        $result = $this->exerciseRunner->runExercise($exercise, $this->file);
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertTrue($result->isSuccessful());
    }

    public function testWhenPreChecksFailTheyReturnImmediatelyEarly()
    {
        $runMe = $this->getMock(CheckInterface::class);
        $runMe
            ->expects($this->once())
            ->method('check')
            ->will($this->returnValue(new Failure($this->check, 'nope')));
        
        $doNotRunMe = $this->getMock(CheckInterface::class);
        $doNotRunMe
            ->expects($this->never())
            ->method('check');

        $solution = $this->getMock(SolutionInterface::class);
        $exercise = $this->getMock(StdOutExercise::class);
        $exercise->expects($this->any())
            ->method('getSolution')
            ->will($this->returnValue($solution));

        $this->exerciseRunner->registerPreCheck($runMe, StdOutExerciseCheck::class);
        $this->exerciseRunner->registerCheck($doNotRunMe, StdOutExerciseCheck::class);

        $result = $this->exerciseRunner->runExercise($exercise, $this->file);
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertFalse($result->isSuccessful());
    }

    public function testSelfCheck()
    {
        $runMe = $this->getMock(CheckInterface::class);
        $this->exerciseRunner->registerCheck($runMe, ExerciseInterface::class);

        $runMe
            ->expects($this->once())
            ->method('check')
            ->will($this->returnValue(new Success($this->check->getName())));

        $solution = $this->getMock(SolutionInterface::class);
        $exercise = $this->getMock(SelfCheckExercise::class, ['getSolution']);
        $exercise->expects($this->any())
            ->method('getSolution')
            ->will($this->returnValue($solution));
        
        $result = $this->exerciseRunner->runExercise($exercise, $this->file);
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertCount(2, $result);
    }

    public function testCodeWhichRequiresPatchingIsModifiedOnDiskAfterPreChecksAndThenReverted()
    {
        file_put_contents($this->file, 'ORIGINAL CONTENT');

        $solution = $this->getMock(SolutionInterface::class);
        $exercise = $this->getMock(ExerciseInterface::class);
        $exercise->expects($this->any())
            ->method('getSolution')
            ->will($this->returnValue($solution));
        
        $runMe = $this->getMock(CheckInterface::class);
        $runMe
            ->expects($this->once())
            ->method('check')
            ->with($exercise, $this->file)
            ->will($this->returnCallback(function (ExerciseInterface $exercise, $file) {
                $this->assertStringEqualsFile($file, 'MODIFIED CONTENT');
                return new Success('test');
            }));
        
        $this->codePatcher
            ->expects($this->once())
            ->method('patch')
            ->with($exercise, 'ORIGINAL CONTENT')
            ->will($this->returnValue('MODIFIED CONTENT'));
        
        $this->exerciseRunner->registerCheck($runMe, ExerciseInterface::class);
        $this->exerciseRunner->runExercise($exercise, $this->file);
        $this->assertStringEqualsFile($this->file, 'ORIGINAL CONTENT');
    }

    public function testIfSolutionRequiresComposerButComposerCannotBeLocatedExceptionIsThrown()
    {
        $refProp = new \ReflectionProperty(ExerciseRunner::class, 'composerLocations');
        $refProp->setAccessible(true);
        $refProp->setValue($this->exerciseRunner, []);
        
        $solution = $this->getMock(SolutionInterface::class);
        $exercise = $this->getMock(StdOutExercise::class);
        $exercise->expects($this->any())
            ->method('getSolution')
            ->will($this->returnValue($solution));

        $solution
            ->expects($this->once())
            ->method('hasComposerFile')
            ->will($this->returnValue(true));

        $this->setExpectedException(RuntimeException::class, 'Composer could not be located on the system');
        $this->exerciseRunner->runExercise($exercise, $this->file);
    }

    public function testIfSolutionRequiresComposerButVendorDirExistsNothingIsDone()
    {
        mkdir(sprintf('%s/vendor',  dirname($this->file)));
        $this->assertFileExists(sprintf('%s/vendor', dirname($this->file)));

        $solution = $this->getMock(SolutionInterface::class);
        $exercise = $this->getMock(StdOutExercise::class);
        $exercise->expects($this->any())
            ->method('getSolution')
            ->will($this->returnValue($solution));

        $solution
            ->expects($this->once())
            ->method('hasComposerFile')
            ->will($this->returnValue(true));

        $solution
            ->expects($this->any())
            ->method('getBaseDirectory')
            ->will($this->returnValue(dirname($this->file)));

        $result = $this->exerciseRunner->runExercise($exercise, $this->file);
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertTrue($result->isSuccessful());
        $this->assertFileExists(sprintf('%s/vendor', dirname($this->file)));
        //check for non existence of lock file, composer generates this when updating if it doesn't exist
        $this->assertFileNotExists(sprintf('%s/composer.lock', dirname($this->file)));
    }

    public function testIfSolutionRequiresComposerComposerInstallIsExecuted()
    {
        $this->assertFileNotExists(sprintf('%s/vendor', dirname($this->file)));
        
        file_put_contents(sprintf('%s/composer.json', dirname($this->file)), json_encode([
            'requires' => [
                'phpunit/phpunit' => '~5.0'  
            ],
        ]));
        
        $solution = $this->getMock(SolutionInterface::class);
        $exercise = $this->getMock(StdOutExercise::class);
        $exercise->expects($this->any())
            ->method('getSolution')
            ->will($this->returnValue($solution));

        $solution
            ->expects($this->once())
            ->method('hasComposerFile')
            ->will($this->returnValue(true));

        $solution
            ->expects($this->any())
            ->method('getBaseDirectory')
            ->will($this->returnValue(dirname($this->file)));

        $result = $this->exerciseRunner->runExercise($exercise, $this->file);
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertTrue($result->isSuccessful());
        $this->assertFileExists(sprintf('%s/vendor', dirname($this->file)));
    }

    public function tearDown()
    {
        $this->filesystem->remove(dirname($this->file));
    }
}
