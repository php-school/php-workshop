<?php

namespace PhpWorkshop\PhpWorkshop\ResultRenderer;

use PhpWorkshop\PhpWorkshop\Result\ResultInterface;
use PhpWorkshop\PhpWorkshop\Result\Success;

/**
 * Class SuccessRenderer
 * @package PhpWorkshop\PhpWorkshop\ResultRenderer
 */
class SuccessRenderer implements ResultRendererInterface
{

    /**
     * @param ResultInterface $result
     * @param ResultsRenderer $renderer
     * @return string
     */
    public function render(ResultInterface $result, ResultsRenderer $renderer)
    {
        if (!$result instanceof Success) {
            throw new \InvalidArgumentException(sprintf('Incompatible result type: %s', get_class($result)));
        }

        return "";
    }
}
