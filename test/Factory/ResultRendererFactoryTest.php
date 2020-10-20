<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Factory\ResultRendererFactory;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultRendererInterface;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ResultRendererFactoryTest extends TestCase
{
    public function testRegisterRendererRequiresResultInterface(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $resultClass = get_class($this->createMock(TestCase::class));
        $rendererClass = get_class($this->createMock(ResultRendererInterface::class));
        $factory = new ResultRendererFactory();
        
        $factory->registerRenderer($resultClass, $rendererClass);
    }

    public function testRegisterRendererRequiresResultRendererInterface(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $resultClass = get_class($this->createMock(ResultInterface::class));
        $rendererClass = get_class($this->createMock(TestCase::class));
        $factory = new ResultRendererFactory();
        
        $factory->registerRenderer($resultClass, $rendererClass);
    }

    public function testRegisterRendererRequiresResultClassToBeString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $resultClass = $this->createMock(ResultInterface::class);
        $rendererClass = get_class($this->createMock(ResultRendererInterface::class));
        $factory = new ResultRendererFactory();
        
        $factory->registerRenderer($resultClass, $rendererClass);
    }

    public function testRegisterRendererRequiresRendererClassToBeString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $resultClass = get_class($this->createMock(ResultInterface::class));
        $rendererClass = $this->createMock(ResultRendererInterface::class);
        $factory = new ResultRendererFactory();
        
        $factory->registerRenderer($resultClass, $rendererClass);
    }

    public function testCreateRequiresMappingToClassName(): void
    {
        $this->expectException(RuntimeException::class);

        $resultClass = $this->createMock(ResultInterface::class);
        $factory = new ResultRendererFactory();

        $factory->create($resultClass);
    }

    public function testCreateReturnsMappedRendererInterface(): void
    {
        $resultClass = $this->createMock(ResultInterface::class);
        $resultClassName = get_class($resultClass);
        $rendererClassName = get_class($this->createMock(ResultRendererInterface::class));
        $factory = new ResultRendererFactory();
        $factory->registerRenderer($resultClassName, $rendererClassName);

        $returnedRenderer = $factory->create($resultClass);

        $this->assertInstanceOf($rendererClassName, $returnedRenderer);
    }

    public function testExceptionIsThrownIfFactoryReturnsInCorrectRenderer(): void
    {
        $resultClass = $this->createMock(ResultInterface::class);
        $resultClassName = get_class($resultClass);
        $rendererClassName = get_class($this->createMock(ResultRendererInterface::class));
        $factory = new ResultRendererFactory();
        $factory->registerRenderer($resultClassName, $rendererClassName, function () {
            return new \stdClass();
        });

        $this->expectException(RuntimeException::class);

        $factory->create($resultClass);
    }
}
