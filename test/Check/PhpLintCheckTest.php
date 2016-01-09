<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Check\PhpLintCheck;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * Class PhpLintCheckTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class PhpLintCheckTest extends PHPUnit_Framework_TestCase
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
        $this->exercise = $this->getMock(ExerciseInterface::class);
        $this->assertEquals('PHP Code Check', $this->check->getName());
        $this->assertEquals(ExerciseInterface::class, $this->check->getExerciseInterface());

        $this->assertTrue($this->check->canRun(ExerciseType::CGI()));
        $this->assertTrue($this->check->canRun(ExerciseType::CLI()));
    }

    public function testSuccess()
    {
        $this->assertInstanceOf(
            Success::class,
            $this->check->check($this->exercise, __DIR__ . '/../res/lint/pass.php')
        );
    }

    public function testFailure()
    {
        $failure = $this->check->check($this->exercise, __DIR__ . '/../res/lint/fail.php');
        $this->assertInstanceOf(Failure::class, $failure);
        $this->assertRegExp(
            "/^PHP Parse error:  syntax error, unexpected end of file, expecting ',' or ';'/",
            $failure->getReason()
        );
    }
}
