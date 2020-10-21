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
    public function render(ResultsRenderer $renderer): string
    {
        return $renderer->center((string) $this->result->getReason()) . "\n";
    }
}
