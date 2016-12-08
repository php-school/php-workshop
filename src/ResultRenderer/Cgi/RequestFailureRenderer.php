<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer\Cgi;

use PhpSchool\PhpWorkshop\Result\Cgi\RequestFailure;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultRendererInterface;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultsRenderer;

/**
 * Renderer for `PhpSchool\PhpWorkshop\Result\Cli\RequestFailure`.
 *
 * @package PhpSchool\PhpWorkshop\ResultRenderer
 */
class RequestFailureRenderer implements ResultRendererInterface
{

    /**
     * @var RequestFailure
     */
    private $result;

    /**
     * @param RequestFailure $result The failure.
     */
    public function __construct(RequestFailure $result)
    {
        $this->result = $result;
    }

    /**
     * Print the actual and expected output.
     *
     * @param ResultsRenderer $renderer
     * @return string
     */
    public function render(ResultsRenderer $renderer)
    {
        $output = '';
        if ($this->result->headersDifferent()) {
            $output .= sprintf(
                "%s      %s\n%s  %s",
                $renderer->style('YOUR HEADERS:', ['bold', 'yellow']),
                $this->headers($this->result->getActualHeaders(), $renderer),
                $renderer->style('EXPECTED HEADERS:', ['bold', 'yellow']),
                $this->headers($this->result->getExpectedHeaders(), $renderer, false)
            );
        }

        if ($this->result->bodyDifferent()) {
            if ($this->result->headersAndBodyDifferent()) {
                $output .= "\n" . $renderer->style('- - - - - - - - -', ['default', 'bold']) . "\n\n";
            }

            $output .= sprintf(
                "%s       %s\n\n%s   %s\n",
                $renderer->style('YOUR OUTPUT:', ['bold', 'yellow']),
                $renderer->style(sprintf('"%s"', $this->result->getActualOutput()), 'red'),
                $renderer->style('EXPECTED OUTPUT:', ['bold', 'yellow']),
                $renderer->style(sprintf('"%s"', $this->result->getExpectedOutput()), 'green')
            );
        }

        return $output;
    }

    /**
     * @param array $headers
     * @param ResultsRenderer $renderer
     * @param bool $actual
     * @return string
     */
    private function headers(array $headers, ResultsRenderer $renderer, $actual = true)
    {
        $indent = false;
        $output = '';
        foreach ($headers as $name => $value) {
            if ($indent) {
                $output .= str_repeat(' ', 19);
            }

            $output .=  $renderer->style(sprintf('%s: %s', $name, $value), $actual ? 'red' : 'green') . "\n";
            $indent  = true;
        }

        return $output;
    }
}
