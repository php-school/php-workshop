<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\CgiOutBodyFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutHeadersFailure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;

/**
 * Class CgiOutHeadersFailureRenderer
 * @package PhpSchool\PhpWorkshop\ResultRenderer
 */
class CgiOutHeadersFailureRenderer implements ResultRendererInterface
{

    /**
     * @param ResultInterface $result
     * @param ResultsRenderer $renderer
     * @return string
     */
    public function render(ResultInterface $result, ResultsRenderer $renderer)
    {
        if (!$result instanceof CgiOutHeadersFailure) {
            throw new \InvalidArgumentException(sprintf('Incompatible result type: %s', get_class($result)));
        }

        return sprintf(
            "  %s\n%s\n  %s\n%s\n",
            $renderer->style("ACTUAL", ['bold', 'underline', 'yellow']),
            $this->headers($result->getActualHeaders(), $renderer),
            $renderer->style("EXPECTED", ['yellow', 'bold', 'underline']),
            $this->headers($result->getExpectedHeaders(), $renderer)
        );
    }

    /**
     * @param array $headers
     * @param ResultsRenderer $renderer
     * @return string
     */
    private function headers(array $headers, ResultsRenderer $renderer)
    {
        $output = '';
        foreach ($headers as $name => $value) {
            $output .= '  ' . $renderer->style(sprintf("%s: %s", $name, $value), 'red') . "\n";
        }
        
        return $output;
    }
}
