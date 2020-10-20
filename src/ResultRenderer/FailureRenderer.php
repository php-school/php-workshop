<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\Failure;

/**
 * Renderer for `PhpSchool\PhpWorkshop\Result\Failure`.
 */
class FailureRenderer implements ResultRendererInterface
{
    /**
     * @var Failure
     */
    private $result;

    /**
     * @param Failure $result The failure.
     */
    public function __construct(Failure $result)
    {
        $this->result = $result;
    }

    /**
     * Simply print the reason.
     *
     * @param ResultsRenderer $renderer
     * @return string
     */
    public function render(ResultsRenderer $renderer)
    {
        return $renderer->center($this->result->getReason()) . "\n";
    }
}
