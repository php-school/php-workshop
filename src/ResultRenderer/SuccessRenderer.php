<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * Class SuccessRenderer
 * @package PhpSchool\PhpWorkshop\ResultRenderer
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
