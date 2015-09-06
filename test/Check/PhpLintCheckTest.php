<?php


namespace PhpWorkshop\PhpWorkshopTest;

use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Check\PhpLintCheck;
use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\Result\Failure;
use PhpWorkshop\PhpWorkshop\Result\Success;

/**
 * Class PhpLintCheckTest
 * @package PhpWorkshop\PhpWorkshopTest
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
        $this->assertTrue($this->check->breakChainOnFailure());

        $this->exercise = $this->getMock(ExerciseInterface::class);
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
