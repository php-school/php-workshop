<?php

namespace PhpWorkshop\PhpWorkshop\ResultRenderer;

use PhpWorkshop\PhpWorkshop\Result\ResultInterface;
use PhpWorkshop\PhpWorkshop\Result\StdOutFailure;

/**
 * Class StdOutFailureRenderer
 * @package PhpWorkshop\PhpWorkshop\ResultRenderer
 */
class StdOutFailureRenderer implements ResultRendererInterface
{

    /**
     * @param ResultInterface $result
     * @param ResultsRenderer $renderer
     * @return string
     */
    public function render(ResultInterface $result, ResultsRenderer $renderer)
    {
        if (!$result instanceof StdOutFailure) {
            throw new \InvalidArgumentException(sprintf('Incompatible result type: %s', get_class($result)));
        }

        return sprintf(
            "  %s\n%s\n\n  %s\n%s\n",
            $renderer->style("ACTUAL", ['bold', 'underline', 'yellow']),
            $this->indent($renderer->style(sprintf('"%s"', $result->getActualOutput()), 'red')),
            $renderer->style("EXPECTED", ['yellow', 'bold', 'underline']),
            $this->indent($renderer->style(sprintf('"%s"', $result->getExpectedOutput()), 'red'))
        );
    }

    /**
     * @param string $string
     * @return string
     */
    private function indent($string)
    {
        return implode(
            "\n",
            array_map(
                function ($line) {
                    return sprintf("  %s", $line);
                },
                explode("\n", $string)
            )
        );
    }
}
