<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Check\ComposerCheck;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseCheck\ComposerExerciseCheck;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshopTest\Asset\ComposerExercise;
use PHPUnit_Framework_TestCase;

/**
 * Class ComposerCheckTest
 * @package PhpSchool\PhpWorkshopTest\Check
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ComposerCheckTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ComposerCheck
     */
    private $check;

    /**
     * @var ExerciseInterface
     */
    private $exercise;

    public function setUp()
    {
        $this->check = new ComposerCheck;
        $this->exercise = new ComposerExercise;
        $this->assertEquals('Composer Dependency Check', $this->check->getName());
        $this->assertEquals(ComposerExerciseCheck::class, $this->check->getExerciseInterface());

        $this->assertTrue($this->check->canRun(ExerciseType::CGI()));
        $this->assertTrue($this->check->canRun(ExerciseType::CLI()));

    }
    
    public function testExceptionIsThrownIfNotValidExercise()
    {
        $exercise = $this->getMock(ExerciseInterface::class);
        $this->setExpectedException(InvalidArgumentException::class);

        $this->check->check($exercise, '');
    }

    public function testCheckReturnsFailureIfNoComposerFile()
    {
        $result = $this->check->check($this->exercise, 'invalid/solution');

        $this->assertInstanceOf(Failure::class, $result);
        $this->assertSame('Composer Dependency Check', $result->getCheckName());
        $this->assertSame('No composer.json file found', $result->getReason());
    }

    public function testCheckReturnsFailureIfNoComposerLockFile()
    {
        $result = $this->check->check($this->exercise, __DIR__ . '/../res/composer/not-locked/solution.php');

        $this->assertInstanceOf(Failure::class, $result);
        $this->assertSame('Composer Dependency Check', $result->getCheckName());
        $this->assertSame('No composer.lock file found', $result->getReason());
    }

    /**
     * @dataProvider dependencyProvider
     *
     * @param string $dependency
     * @param string $solutionFile
     */
    public function testCheckReturnsFailureIfDependencyNotRequired($dependency, $solutionFile)
    {
        $exercise = $this->getMock(ComposerExercise::class);
        $exercise->expects($this->once())
            ->method('getRequiredPackages')
            ->will($this->returnValue([$dependency]));
        
        $result = $this->check->check($exercise, $solutionFile);

        $this->assertInstanceOf(Failure::class, $result);
        $this->assertSame('Composer Dependency Check', $result->getCheckName());
        $this->assertSame(
            sprintf('Lockfile doesn\'t include the following packages at any version: "%s"', $dependency),
            $result->getReason()
        );
    }

    /**
     * @return array
     */
    public function dependencyProvider()
    {
        return [
            ['klein/klein',           __DIR__ . '/../res/composer/no-klein/solution.php'],
            ['danielstjules/stringy', __DIR__ . '/../res/composer/no-stringy/solution.php']
        ];
    }

    public function testCheckReturnsSuccessIfCorrectLockFile()
    {
        $result = $this->check->check($this->exercise, __DIR__ . '/../res/composer/good-solution/solution.php');

        $this->assertInstanceOf(Success::class, $result);
        $this->assertSame('Composer Dependency Check', $result->getCheckName());
    }
}
