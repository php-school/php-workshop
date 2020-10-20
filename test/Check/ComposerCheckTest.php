<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Check\ComposerCheck;
use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseCheck\ComposerExerciseCheck;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshopTest\Asset\ComposerExercise;
use PHPUnit\Framework\TestCase;

/**
 * Class ComposerCheckTest
 * @package PhpSchool\PhpWorkshopTest\Check
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ComposerCheckTest extends TestCase
{
    /**
     * @var ComposerCheck
     */
    private $check;

    /**
     * @var ExerciseInterface
     */
    private $exercise;

    public function setUp(): void
    {
        $this->check = new ComposerCheck();
        $this->exercise = new ComposerExercise();
        $this->assertEquals('Composer Dependency Check', $this->check->getName());
        $this->assertEquals(ComposerExerciseCheck::class, $this->check->getExerciseInterface());
        $this->assertEquals(SimpleCheckInterface::CHECK_BEFORE, $this->check->getPosition());

        $this->assertTrue($this->check->canRun(ExerciseType::CGI()));
        $this->assertTrue($this->check->canRun(ExerciseType::CLI()));
    }
    
    public function testExceptionIsThrownIfNotValidExercise(): void
    {
        $exercise = $this->createMock(ExerciseInterface::class);
        $this->expectException(InvalidArgumentException::class);

        $this->check->check($exercise, new Input('app'));
    }

    public function testCheckReturnsFailureIfNoComposerFile(): void
    {
        $result = $this->check->check(
            $this->exercise,
            new Input('app', ['program' => 'invalid/solution'])
        );

        $this->assertInstanceOf(Failure::class, $result);
        $this->assertSame('Composer Dependency Check', $result->getCheckName());
        $this->assertSame('No composer.json file found', $result->getReason());
    }

    public function testCheckReturnsFailureIfNoComposerLockFile(): void
    {
        $result = $this->check->check(
            $this->exercise,
            new Input('app', ['program' => __DIR__ . '/../res/composer/not-locked/solution.php'])
        );

        $this->assertInstanceOf(Failure::class, $result);
        $this->assertSame('Composer Dependency Check', $result->getCheckName());
        $this->assertSame('No composer.lock file found', $result->getReason());
    }

    public function testCheckReturnsFailureIfNoVendorFolder(): void
    {
        $result = $this->check->check(
            $this->exercise,
            new Input('app', ['program' => __DIR__ . '/../res/composer/no-vendor/solution.php'])
        );

        $this->assertInstanceOf(Failure::class, $result);
        $this->assertSame('Composer Dependency Check', $result->getCheckName());
        $this->assertSame('No vendor folder found', $result->getReason());
    }

    /**
     * @dataProvider dependencyProvider
     *
     * @param string $dependency
     * @param string $solutionFile
     */
    public function testCheckReturnsFailureIfDependencyNotRequired($dependency, $solutionFile): void
    {
        $exercise = $this->createMock(ComposerExercise::class);
        $exercise->expects($this->once())
            ->method('getRequiredPackages')
            ->willReturn([$dependency]);

        $result = $this->check->check($exercise, new Input('app', ['program' => $solutionFile]));

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
    public function dependencyProvider(): array
    {
        return [
            ['klein/klein',           __DIR__ . '/../res/composer/no-klein/solution.php'],
            ['danielstjules/stringy', __DIR__ . '/../res/composer/no-stringy/solution.php']
        ];
    }

    public function testCheckReturnsSuccessIfCorrectLockFile(): void
    {
        $result = $this->check->check(
            $this->exercise,
            new Input('app', ['program' => __DIR__ . '/../res/composer/good-solution/solution.php'])
        );

        $this->assertInstanceOf(Success::class, $result);
        $this->assertSame('Composer Dependency Check', $result->getCheckName());
    }
}
