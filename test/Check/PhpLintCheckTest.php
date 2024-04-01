<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Check\PhpLintCheck;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;

class PhpLintCheckTest extends TestCase
{
    use AssertionRenames;

    /**
     * @var PhpLintCheck
     */
    private $check;

    /**
     * @var ExerciseInterface
     */
    private $exercise;

    public function setUp(): void
    {
        $this->check = new PhpLintCheck();
        $this->exercise = $this->createMock(ExerciseInterface::class);
        $this->assertEquals('PHP Code Check', $this->check->getName());
        $this->assertEquals(ExerciseInterface::class, $this->check->getExerciseInterface());
        $this->assertEquals(SimpleCheckInterface::CHECK_BEFORE, $this->check->getPosition());

        $this->assertTrue($this->check->canRun(ExerciseType::CGI()));
        $this->assertTrue($this->check->canRun(ExerciseType::CLI()));
    }

    public function testSuccess(): void
    {
        $context = TestContext::withEnvironment($this->exercise);
        $context->importSolution(__DIR__ . '/../res/lint/pass.php');

        $res = $this->check->check($context->getExecutionContext());

        $this->assertInstanceOf(
            Success::class,
            $this->check->check($context->getExecutionContext())
        );
    }

    public function testFailure(): void
    {
        $context = TestContext::withEnvironment($this->exercise,);
        $context->importSolution(__DIR__ . '/../res/lint/fail.php');

        $failure = $this->check->check($context->getExecutionContext());

        $this->assertInstanceOf(Failure::class, $failure);
        $this->assertMatchesRegularExpression(
            "/(PHP )?Parse error:\W+syntax error, unexpected end of file, expecting ['\"][,;]['\"] or ['\"][;,]['\"]/",
            $failure->getReason()
        );
    }
}
