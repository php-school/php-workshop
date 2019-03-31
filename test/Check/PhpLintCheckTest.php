<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Input\Input;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Check\PhpLintCheck;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * Class PhpLintCheckTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class PhpLintCheckTest extends TestCase
{

    /**
     * @var PhpLintCheck
     */
    private $check;

    /**
     * @var ExerciseInterface
     */
    private $exercise;

    public function setUp()
    {
        $this->check = new PhpLintCheck;
        $this->exercise = $this->createMock(ExerciseInterface::class);
        $this->assertEquals('PHP Code Check', $this->check->getName());
        $this->assertEquals(ExerciseInterface::class, $this->check->getExerciseInterface());
        $this->assertEquals(SimpleCheckInterface::CHECK_BEFORE, $this->check->getPosition());

        $this->assertTrue($this->check->canRun(ExerciseType::CGI()));
        $this->assertTrue($this->check->canRun(ExerciseType::CLI()));
    }

    public function testSuccess() : void
    {
        $this->assertInstanceOf(
            Success::class,
            $this->check->check($this->exercise, new Input('app', ['program' => __DIR__ . '/../res/lint/pass.php']))
        );
    }

    public function testFailure() : void
    {
        $failure = $this->check->check(
            $this->exercise,
            new Input('app', ['program' => __DIR__ . '/../res/lint/fail.php'])
        );
        $this->assertInstanceOf(Failure::class, $failure);
        $this->assertRegExp(
            "/^Parse error: syntax error, unexpected end of file, expecting ',' or ';'/",
            $failure->getReason()
        );
    }
}
