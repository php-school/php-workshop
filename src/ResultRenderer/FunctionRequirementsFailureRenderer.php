<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\FunctionRequirementsFailure;

/**
 * Renderer for `PhpSchool\PhpWorkshop\Result\FunctionRequirementsFailure`.
 */
class FunctionRequirementsFailureRenderer implements ResultRendererInterface
{
    /**
     * @var FunctionRequirementsFailure
     */
    private $result;

    /**
     * @param FunctionRequirementsFailure $result The failure.
     */
    public function __construct(FunctionRequirementsFailure $result)
    {
        $this->result = $result;
    }

    /**
     * Print a list of the missing, required functions & print a list of used but banned functions.
     *
     * @param ResultsRenderer $renderer
     * @return string
     */
    public function render(ResultsRenderer $renderer)
    {
        $output = '';
        if (count($bannedFunctions = $this->result->getBannedFunctions())) {
            $output .= sprintf(
                "  %s\n%s\n",
                $renderer->style(
                    "Some functions were used which should not be used in this exercise",
                    ['bold', 'underline', 'yellow']
                ),
                implode("\n", array_map(function (array $call) {
                    return sprintf('    %s on line %s', $call['function'], $call['line']);
                }, $bannedFunctions))
            );
        }

        if (count($missingFunctions = $this->result->getMissingFunctions())) {
            $output .= sprintf(
                "  %s\n%s\n",
                $renderer->style(
                    "Some function requirements were missing. You should use the functions",
                    ['bold', 'underline', 'yellow']
                ),
                implode("\n", array_map(function ($function) {
                    return sprintf('    %s', $function);
                }, $missingFunctions))
            );
        }

        return $output;
    }
}
