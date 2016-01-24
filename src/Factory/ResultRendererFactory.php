<?php

namespace PhpSchool\PhpWorkshop\Factory;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Result\CgiOutResult;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\FunctionRequirementsFailure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;
use PhpSchool\PhpWorkshop\ResultRenderer\CgiOutResultRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\FailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\FunctionRequirementsFailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\OutputFailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultRendererInterface;

/**
 * Class ResultRendererFactory
 * @package PhpSchool\PhpWorkshop\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ResultRendererFactory
{
    /**
     * @var array
     */
    private $mappings = [
        StdOutFailure::class                => OutputFailureRenderer::class,
        CgiOutResult::class                 => CgiOutResultRenderer::class,
        FunctionRequirementsFailure::class  => FunctionRequirementsFailureRenderer::class,
        Failure::class                      => FailureRenderer::class,
    ];

    /**
     * @param string $resultClass
     * @param string $rendererClass
     */
    public function registerRenderer($resultClass, $rendererClass)
    {
        if (!class_implements($resultClass, ResultInterface::class)) {
            throw new InvalidArgumentException;
        }

        if (!class_implements($rendererClass, ResultRendererInterface::class)) {
            throw new InvalidArgumentException;
        }

        $this->mappings[$resultClass] = $rendererClass;
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

        return new $this->mappings[$class]($result);
    }
}
