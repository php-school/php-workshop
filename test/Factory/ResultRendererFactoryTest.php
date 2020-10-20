<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Factory\ResultRendererFactory;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultRendererInterface;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Class ResultsRendererFactoryTest
 * @package PhpSchool\PhpWorkshopTest\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ResultRendererFactoryTest extends TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testRegisterRendererRequiresResultInterface() : void
    {
        $resultClass = get_class($this->createMock(TestCase::class));
        $rendererClass = get_class($this->createMock(ResultRendererInterface::class));
        $factory = new ResultRendererFactory();
        
        $factory->registerRenderer($resultClass, $rendererClass);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRegisterRendererRequiresResultRendererInterface() : void
    {
        $resultClass = get_class($this->createMock(ResultInterface::class));
        $rendererClass = get_class($this->createMock(TestCase::class));
        $factory = new ResultRendererFactory();
        
        $factory->registerRenderer($resultClass, $rendererClass);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRegisterRendererRequiresResultClassToBeString() : void
    {
        $resultClass = $this->createMock(ResultInterface::class);
        $rendererClass = get_class($this->createMock(ResultRendererInterface::class));
        $factory = new ResultRendererFactory();
        
        $factory->registerRenderer($resultClass, $rendererClass);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRegisterRendererRequiresRendererClassToBeString() : void
    {
        $resultClass = get_class($this->createMock(ResultInterface::class));
        $rendererClass = $this->createMock(ResultRendererInterface::class);
        $factory = new ResultRendererFactory();
        
        $factory->registerRenderer($resultClass, $rendererClass);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCreateRequiresMappingToClassName() : void
    {
        $resultClass = $this->createMock(ResultInterface::class);
        $factory = new ResultRendererFactory();

        $factory->create($resultClass);
    }

    public function testCreateReturnsMappedRendererInterface() : void
    {
        $resultClass = $this->createMock(ResultInterface::class);
        $resultClassName = get_class($resultClass);
        $rendererClassName = get_class($this->createMock(ResultRendererInterface::class));
        $factory = new ResultRendererFactory();
        $factory->registerRenderer($resultClassName, $rendererClassName);

        $returnedRenderer = $factory->create($resultClass);

        $this->assertInstanceOf($rendererClassName, $returnedRenderer);
    }

    public function testExceptionIsThrownIfFactoryReturnsInCorrectRenderer() : void
    {
        $resultClass = $this->createMock(ResultInterface::class);
        $resultClassName = get_class($resultClass);
        $rendererClassName = get_class($this->createMock(ResultRendererInterface::class));
        $factory = new ResultRendererFactory();
        $factory->registerRenderer($resultClassName, $rendererClassName, function () {
            return new \stdClass;
        });

        $this->expectException(RuntimeException::class);

        $factory->create($resultClass);
    }
}
