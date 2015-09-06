<?php


namespace PhpWorkshop\PhpWorkshopTest\Exercise;

use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Exercise\HelloWorld;

/**
 * Class HelloWorldTest
 * @package PhpWorkshop\PhpWorkshopTest\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class HelloWorldTest extends PHPUnit_Framework_TestCase
{
    public function testHelloWorldExercise()
    {
        $e = new HelloWorld;
        $this->assertEquals('Hello World', $e->getName());
        $this->assertEquals('Simple Hello World exercise', $e->getDescription());
        $this->assertEquals([], $e->getArgs());

        $this->assertFileExists(realpath($e->getSolution()));
        $this->assertFileExists(realpath($e->getProblem()));
    }
}
