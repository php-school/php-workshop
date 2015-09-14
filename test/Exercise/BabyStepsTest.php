<?php


namespace PhpWorkshop\PhpWorkshopTest\Exercise;

use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Exercise\BabySteps;

/**
 * Class BabyStepsTest
 * @package PhpWorkshop\PhpWorkshopTest\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BabyStepsTest extends PHPUnit_Framework_TestCase
{
    public function testHelloWorldExercise()
    {
        $e = new BabySteps;
        $this->assertEquals('Baby Steps', $e->getName());
        $this->assertEquals('Simple Addition', $e->getDescription());

        $args = $e->getArgs();

        foreach ($args as $arg) {
            $this->assertInternalType('int', $arg);
        }

        $this->assertFileExists(realpath($e->getSolution()));
        $this->assertFileExists(realpath($e->getProblem()));
        $this->assertNull($e->tearDown());
    }
}
