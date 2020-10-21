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
     * @var array<class-string, class-string>
     */
    private $mappings = [];

    /**
     * @var array<class-string, callable>
     */
    private $factories = [];

    /**
     * @param class-string $resultClass
     * @param class-string $rendererClass
     * @param callable|null $factory
     */
    public function registerRenderer(string $resultClass, string $rendererClass, callable $factory = null): void
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
    public function create(ResultInterface $result): ResultRendererInterface
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

    /**
     * @param class-string $implementationName
     * @param class-string $className
     * @return bool
     */
    protected function isImplementationNameOfClass(string $implementationName, string $className): bool
    {
        return is_subclass_of($implementationName, $className);
    }
}
