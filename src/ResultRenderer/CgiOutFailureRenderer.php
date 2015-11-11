<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\CgiOutFailure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * Class CgiOutFailureRenderer
 * @package PhpSchool\PhpWorkshop\ResultRenderer
 */
class CgiOutFailureRenderer implements ResultRendererInterface
{

    /**
     * @param ResultInterface $result
     * @param ResultsRenderer $renderer
     * @return string
     */
    public function render(ResultInterface $result, ResultsRenderer $renderer)
    {
        if (!$result instanceof CgiOutFailure) {
            throw new \InvalidArgumentException(sprintf('Incompatible result type: %s', get_class($result)));
        }
        
        $output = '';
        if ($result->headersDifferent()) {
            $output .= sprintf(
                "  %s\n%s\n  %s\n%s\n",
                $renderer->style("ACTUAL", ['bold', 'underline', 'yellow']),
                $this->headers($result->getActualHeaders(), $renderer),
                $renderer->style("EXPECTED", ['yellow', 'bold', 'underline']),
                $this->headers($result->getExpectedHeaders(), $renderer)
            );
        }
        
        if ($result->bodyDifferent()) {
            if ($output !== '') {
                $output .= "\n";
            }

            $output .= sprintf(
                "  %s\n%s\n\n  %s\n%s\n",
                $renderer->style("ACTUAL", ['bold', 'underline', 'yellow']),
                $this->indent($renderer->style(sprintf('"%s"', $result->getActualOutput()), 'red')),
                $renderer->style("EXPECTED", ['yellow', 'bold', 'underline']),
                $this->indent($renderer->style(sprintf('"%s"', $result->getExpectedOutput()), 'red'))
            );
        }
        
        return $output;
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
