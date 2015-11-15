<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\CgiOutResult;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\SuccessInterface;

/**
 * Class CgiOutResultRenderer
 * @package PhpSchool\PhpWorkshop\ResultRenderer
 */
class CgiOutResultRenderer implements ResultRendererInterface
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
        foreach ($result as $key => $request) {
            if ($request instanceof SuccessInterface) {
                continue;
            }
            
            $output .= "\n";

            if ($request->headersDifferent()) {
                $output .= sprintf(
                    "  %s    %s  %s  %s\n",
                    $renderer->style(sprintf("%d. ACTUAL HEADERS:", $key + 1), ['bold', 'yellow']),
                    $this->headers($request->getActualHeaders(), $renderer),
                    $renderer->style(sprintf("%d. EXPECTED HEADERS:", $key + 1), ['bold', 'yellow']),
                    $this->headers($request->getExpectedHeaders(), $renderer)
                );
            }

            if ($request->bodyDifferent()) {
                $output .= sprintf(
                    "  %s    %s\n  %s  %s\n",
                    $renderer->style(sprintf("%d. ACTUAL CONTENT:", $key + 1), ['bold', 'yellow']),
                    $renderer->style(sprintf('"%s"', $request->getActualOutput()), 'red'),
                    $renderer->style(sprintf("%d. EXPECTED CONTENT:", $key + 1), ['bold', 'yellow']),
                    $renderer->style(sprintf('"%s"', $request->getExpectedOutput()), 'red')
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
        $indent = false;
        $output = '';
        foreach ($headers as $name => $value) {
            if ($indent) {
                $output .= str_repeat(' ', 24);
            }
            
            $output .=  $renderer->style(sprintf("%s: %s", $name, $value), 'red') . "\n";
            $indent  = true;
        }
        
        return $output;
    }
}
