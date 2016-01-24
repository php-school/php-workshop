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
     * @var Failure
     */
    private $result;

    /**
     * @param Failure $result
     */
    public function __construct(Failure $result)
    {
        $this->result = $result;
    }

    /**
     * @param ResultsRenderer $renderer
     * @return string
     */
    public function render(ResultsRenderer $renderer)
    {
        return '  ' . $this->result->getReason() . "\n";
    }
}
