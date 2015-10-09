<?php

namespace PhpWorkshop\PhpWorkshop\ResultRenderer;

use PhpWorkshop\PhpWorkshop\Result\Failure;
use PhpWorkshop\PhpWorkshop\Result\ResultInterface;

/**
 * Class FailureRenderer
 * @package PhpWorkshop\PhpWorkshop\ResultRenderer
 */
class FailureRenderer implements ResultRendererInterface
{

    /**
     * @param ResultInterface $result
     * @return string
     */
    public function render(ResultInterface $result)
    {
        if (!$result instanceof Failure) {
            throw new \InvalidArgumentException(sprintf('Incompatible result type: %s', get_class($result)));
        }

        return $result->getReason();
    }
}
