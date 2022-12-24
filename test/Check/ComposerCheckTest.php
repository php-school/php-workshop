<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Check\ComposerCheck;
use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseCheck\ComposerExerciseCheck;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\ComposerFailure;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshopTest\Asset\ComposerExercise;
use PHPUnit\Framework\TestCase;

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

        $this->assertInstanceOf(ComposerFailure::class, $result);
        $this->assertSame('Composer Dependency Check', $result->getCheckName());
        $this->assertTrue($result->isMissingComponent());
        $this->assertSame('composer.json', $result->getMissingComponent());
    }

    public function testCheckReturnsFailureIfNoComposerLockFile(): void
    {
        $result = $this->check->check(
            $this->exercise,
            new Input('app', ['program' => __DIR__ . '/../res/composer/not-locked/solution.php'])
        );

        $this->assertInstanceOf(ComposerFailure::class, $result);
        $this->assertSame('Composer Dependency Check', $result->getCheckName());
        $this->assertTrue($result->isMissingComponent());
        $this->assertSame('composer.lock', $result->getMissingComponent());
    }

    public function testCheckReturnsFailureIfNoVendorFolder(): void
    {
        $result = $this->check->check(
            $this->exercise,
            new Input('app', ['program' => __DIR__ . '/../res/composer/no-vendor/solution.php'])
        );

        $this->assertInstanceOf(ComposerFailure::class, $result);
        $this->assertSame('Composer Dependency Check', $result->getCheckName());
        $this->assertTrue($result->isMissingComponent());
        $this->assertSame('vendor', $result->getMissingComponent());
    }

    /**
     * @dataProvider dependencyProvider
     *
     * @param string $dependency
     * @param string $solutionFile
     */
    public function testCheckReturnsFailureIfDependencyNotRequired(string $dependency, string $solutionFile): void
    {
        $exercise = $this->createMock(ComposerExercise::class);
        $exercise->expects($this->once())
            ->method('getRequiredPackages')
            ->willReturn([$dependency]);

        $result = $this->check->check($exercise, new Input('app', ['program' => $solutionFile]));

        $this->assertInstanceOf(ComposerFailure::class, $result);
        $this->assertSame('Composer Dependency Check', $result->getCheckName());
        $this->assertTrue($result->isMissingPackages());
        $this->assertSame([$dependency], $result->getMissingPackages());
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
