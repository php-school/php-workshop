<?php

namespace PhpWorkshop\PhpWorkshop\ResultRenderer;

use PhpWorkshop\PhpWorkshop\Result\ResultInterface;

/**
 * Interface ResultRendererInterface
 * @package PhpWorkshop\PhpWorkshop\ResultRenderer
 */
interface ResultRendererInterface
{

    /**
     * @param ResultInterface $result
     * @return string
     */
    public function render(ResultInterface $result);
}