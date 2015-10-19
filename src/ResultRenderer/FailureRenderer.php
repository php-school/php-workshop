<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * Class FailureRenderer
 * @package PhpSchool\PhpWorkshop\ResultRenderer
 */
class FailureRenderer implements ResultRendererInterface
{

    /**
     * @param ResultInterface $result
     * @param ResultsRenderer $renderer
     * @return string
     */
    public function render(ResultInterface $result, ResultsRenderer $renderer)
    {
        if (!$result instanceof Failure) {
            throw new \InvalidArgumentException(sprintf('Incompatible result type: %s', get_class($result)));
        }

        return $result->getReason();
    }
}
