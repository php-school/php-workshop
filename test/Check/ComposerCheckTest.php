<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Check\ComposerCheck;
use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseCheck\ComposerExerciseCheck;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\ComposerFailure;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshopTest\Asset\ComposerExercise;
use PHPUnit\Framework\TestCase;

class ComposerCheckTest extends TestCase
{
    private ComposerCheck $check;
    private ComposerExercise $exercise;

    public function setUp(): void
    {
        $this->check = new ComposerCheck();
        $this->exercise = new ComposerExercise();
    }

    public function testCheckMeta(): void
    {
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

        $this->check->check(TestContext::withoutEnvironment());
    }

    public function testCheckReturnsFailureIfNoComposerFile(): void
    {
        $result = $this->check->check(
            TestContext::withoutEnvironment($this->exercise)
        );

        $this->assertInstanceOf(ComposerFailure::class, $result);
        $this->assertSame('Composer Dependency Check', $result->getCheckName());
        $this->assertTrue($result->isMissingComponent());
        $this->assertSame('composer.json', $result->getMissingComponent());
    }

    public function testCheckReturnsFailureIfNoComposerLockFile(): void
    {
        $context = TestContext::withEnvironment($this->exercise);
        $context->importStudentSolutionFolder(__DIR__ . '/../res/composer/not-locked/');

        $result = $this->check->check($context);

        $this->assertInstanceOf(ComposerFailure::class, $result);
        $this->assertSame('Composer Dependency Check', $result->getCheckName());
        $this->assertTrue($result->isMissingComponent());
        $this->assertSame('composer.lock', $result->getMissingComponent());
    }

    public function testCheckReturnsFailureIfNoVendorFolder(): void
    {
        $context = TestContext::withEnvironment($this->exercise);
        $context->importStudentSolutionFolder(__DIR__ . '/../res/composer/no-vendor/');

        $result = $this->check->check($context);

        $this->assertInstanceOf(ComposerFailure::class, $result);
        $this->assertSame('Composer Dependency Check', $result->getCheckName());
        $this->assertTrue($result->isMissingComponent());
        $this->assertSame('vendor', $result->getMissingComponent());
    }

    /**
     * @dataProvider dependencyProvider
     */
    public function testCheckReturnsFailureIfDependencyNotRequired(string $dependency, string $solutionFolder): void
    {
        $exercise = $this->createMock(ComposerExercise::class);
        $exercise->expects($this->once())
            ->method('getRequiredPackages')
            ->willReturn([$dependency]);

        $context = TestContext::withEnvironment($exercise);
        $context->importStudentSolutionFolder($solutionFolder);

        $result = $this->check->check($context);

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
            ['klein/klein',           __DIR__ . '/../res/composer/no-klein'],
            ['danielstjules/stringy', __DIR__ . '/../res/composer/no-stringy']
        ];
    }

    public function testCheckReturnsSuccessIfCorrectLockFile(): void
    {
        $context = TestContext::withEnvironment($this->exercise);
        $context->importStudentSolutionFolder(__DIR__ . '/../res/composer/good-solution');

        $result = $this->check->check($context);

        $this->assertInstanceOf(Success::class, $result);
        $this->assertSame('Composer Dependency Check', $result->getCheckName());
    }
}
