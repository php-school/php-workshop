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
        if ($this->result->getExpectedOutput()) {
            return sprintf(
                "  %s\n%s\n\n  %s\n%s\n" . ($this->result->getErrors() ? "\n  %s\n%s\n" : ""),
                $renderer->style("ACTUAL", ['bold', 'underline', 'yellow']),
                $this->indent($renderer->style(sprintf('"%s"', $this->result->getActualOutput()), 'red')),
                $renderer->style("EXPECTED", ['yellow', 'bold', 'underline']),
                $this->indent($renderer->style(sprintf('"%s"', $this->result->getExpectedOutput()), 'red')),
                $renderer->style("ERRORS/WARNINGS", ['yellow', 'bold', 'underline']),
                $this->indent($renderer->style(sprintf('%s', $this->result->getErrors()), 'red'))
            );
        } else {
            return '  ' . $this->result->getReason() . "\n";
        }
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
