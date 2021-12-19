<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\ResultRenderer;

/**
 * The interface, result renderers should adhere to.
 */
interface ResultRendererInterface
{
    /**
     * This method should return a string representation of the result,
     * formatted for output on the command line.
     *
     * The `PhpSchool\PhpWorkshop\ResultRenderer\ResultsRenderer` method has
     * various helpers to render line breaks, colour output and can also render child
     * results.
     *
     * @param ResultsRenderer $renderer The main renderer instance.
     * @return string The string representation of the result.
     */
    public function render(ResultsRenderer $renderer): string;
}
