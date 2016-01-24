<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * Interface ResultRendererInterface
 * @package PhpSchool\PhpWorkshop\ResultRenderer
 */
interface ResultRendererInterface
{

    /**
     * @param ResultsRenderer $renderer
     * @return string
     */
    public function render(ResultsRenderer $renderer);
}
