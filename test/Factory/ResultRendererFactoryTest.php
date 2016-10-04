<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Factory\ResultRendererFactory;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultRendererInterface;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PHPUnit_Framework_TestCase;
use RuntimeException;

/**
 * Class ResultsRendererFactoryTest
 * @package PhpSchool\PhpWorkshopTest\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ResultsRendererFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testRegisterRendererRequiresResultInterface()
    {
        $resultClass = get_class($this->createMock(PHPUnit_Framework_TestCase::class));
        $rendererClass = get_class($this->createMock(ResultRendererInterface::class));
        $factory = new ResultRendererFactory();
        
        $factory->registerRenderer($resultClass, $rendererClass);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRegisterRendererRequiresResultRendererInterface()
    {
        $resultClass = get_class($this->createMock(ResultInterface::class));
        $rendererClass = get_class($this->createMock(PHPUnit_Framework_TestCase::class));
        $factory = new ResultRendererFactory();
        
        $factory->registerRenderer($resultClass, $rendererClass);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRegisterRendererRequiresResultClassToBeString()
    {
        $resultClass = $this->createMock(ResultInterface::class);
        $rendererClass = get_class($this->createMock(ResultRendererInterface::class));
        $factory = new ResultRendererFactory();
        
        $factory->registerRenderer($resultClass, $rendererClass);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRegisterRendererRequiresRendererClassToBeString()
    {
        $resultClass = get_class($this->createMock(ResultInterface::class));
        $rendererClass = $this->createMock(ResultRendererInterface::class);
        $factory = new ResultRendererFactory();
        
        $factory->registerRenderer($resultClass, $rendererClass);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCreateRequiresMappingToClassName()
    {
        $resultClass = $this->createMock(ResultInterface::class);
        $factory = new ResultRendererFactory();

        $factory->create($resultClass);
    }

    public function testCreateReturnsMappedRendererInterface()
    {
        $resultClass = $this->createMock(ResultInterface::class);
        $resultClassName = get_class($resultClass);
        $rendererClassName = get_class($this->createMock(ResultRendererInterface::class));
        $factory = new ResultRendererFactory();
        $factory->registerRenderer($resultClassName, $rendererClassName);

        $returnedRenderer = $factory->create($resultClass);

        $this->assertInstanceOf($rendererClassName, $returnedRenderer);
    }
}
