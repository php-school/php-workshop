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
     * @param ResultInterface $result
     * @param ResultsRenderer $renderer
     * @return string
     */
    public function render(ResultInterface $result, ResultsRenderer $renderer);
}
