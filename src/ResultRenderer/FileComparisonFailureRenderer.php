<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\FileComparisonFailure;

/**
 * Renderer for `PhpSchool\PhpWorkshop\Result\FileComparisonFailure`.
 */
class FileComparisonFailureRenderer implements ResultRendererInterface
{
    /**
     * @var FileComparisonFailure
     */
    private $result;

    /**
     * @param FileComparisonFailure $result The failure.
     */
    public function __construct(FileComparisonFailure $result)
    {
        $this->result = $result;
    }

    /**
     * Print the actual and expected output.
     *
     * @param ResultsRenderer $renderer
     * @return string
     */
    public function render(ResultsRenderer $renderer): string
    {
        return sprintf(
            "  %s%s\n%s\n\n  %s%s\n%s\n",
            $renderer->style('YOUR OUTPUT FOR: ', ['bold', 'yellow']),
            $renderer->style($this->result->getFileName(), ['bold', 'green']),
            $this->indent($renderer->style(sprintf('"%s"', $this->result->getActualValue()), 'red')),
            $renderer->style('EXPECTED OUTPUT FOR: ', ['bold', 'yellow']),
            $renderer->style($this->result->getFileName(), ['bold', 'green']),
            $this->indent($renderer->style(sprintf('"%s"', $this->result->getExpectedValue()), 'green'))
        );
    }

    /**
     * @param string $string
     * @return string
     */
    private function indent(string $string): string
    {
        return implode(
            "\n",
            array_map(
                function ($line) {
                    return sprintf('  %s', $line);
                },
                explode("\n", $string)
            )
        );
    }
}
