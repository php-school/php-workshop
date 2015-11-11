<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\CgiOutResult;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\SuccessInterface;

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
        if (!$result instanceof CgiOutResult) {
            throw new \InvalidArgumentException(sprintf('Incompatible result type: %s', get_class($result)));
        }

        $output = '';
        foreach ($result as $request) {
            if ($request instanceof SuccessInterface) {
                continue;
            }

            if ($request->headersDifferent()) {
                $output .= sprintf(
                    "  %s\n%s\n  %s\n%s\n",
                    $renderer->style("ACTUAL", ['bold', 'underline', 'yellow']),
                    $this->headers($request->getActualHeaders(), $renderer),
                    $renderer->style("EXPECTED", ['yellow', 'bold', 'underline']),
                    $this->headers($request->getExpectedHeaders(), $renderer)
                );
            }

            if ($request->bodyDifferent()) {
                if ($output !== '') {
                    $output .= "\n";
                }

                $output .= sprintf(
                    "  %s\n%s\n\n  %s\n%s\n",
                    $renderer->style("ACTUAL", ['bold', 'underline', 'yellow']),
                    $this->indent($renderer->style(sprintf('"%s"', $request->getActualOutput()), 'red')),
                    $renderer->style("EXPECTED", ['yellow', 'bold', 'underline']),
                    $this->indent($renderer->style(sprintf('"%s"', $request->getExpectedOutput()), 'red'))
                );
            }
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
