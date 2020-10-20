<?php

namespace PhpSchool\PhpWorkshop\Factory;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultRendererInterface;

/**
 * Manages and creates renderers for results
 */
class ResultRendererFactory
{
    /**
     * @var array
     */
    private $mappings = [];

    /**
     * @var array
     */
    private $factories = [];

    /**
     * @param string $resultClass
     * @param string $rendererClass
     * @param callable $factory
     */
    public function registerRenderer($resultClass, $rendererClass, callable $factory = null)
    {
        if (!$this->isImplementationNameOfClass($resultClass, ResultInterface::class)) {
            throw new InvalidArgumentException();
        }

        if (!$this->isImplementationNameOfClass($rendererClass, ResultRendererInterface::class)) {
            throw new InvalidArgumentException();
        }

        $this->mappings[$resultClass] = $rendererClass;

        $this->factories[$rendererClass] = $factory ?: function (ResultInterface $result) use ($rendererClass) {
            return new $rendererClass($result);
        };
    }

    /**
     * @param ResultInterface $result
     * @return ResultRendererInterface
     */
    public function create(ResultInterface $result)
    {
        $class = get_class($result);
        if (!isset($this->mappings[$class])) {
            throw new \RuntimeException(sprintf('No renderer found for "%s"', $class));
        }

        $class = $this->mappings[$class];
        $factory = $this->factories[$class];

        $renderer = $factory($result);

        if (!$renderer instanceof $class) {
            throw new \RuntimeException(
                sprintf(
                    'Renderer Factory for "%s" produced "%s" instead of expected "%s"',
                    $class,
                    is_object($renderer) ? get_class($renderer) : gettype($renderer),
                    $class
                )
            );
        }

        return $renderer;
    }

    protected function isImplementationNameOfClass($implementationName, $className)
    {
        return is_string($implementationName) && is_subclass_of($implementationName, $className);
    }
}
