<?php

namespace PhpWorkshop\PhpWorkshop\ResultRenderer;

use PhpWorkshop\PhpWorkshop\Result\FunctionRequirementsFailure;
use PhpWorkshop\PhpWorkshop\Result\ResultInterface;

/**
 * Class FunctionRequirementsFailureRenderer
 * @package PhpWorkshop\PhpWorkshop\ResultRenderer
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FunctionRequirementsFailureRenderer implements ResultRendererInterface
{

    /**
     * @param ResultInterface $result
     * @param ResultsRenderer $renderer
     * @return string
     */
    public function render(ResultInterface $result, ResultsRenderer $renderer)
    {
        if (!$result instanceof FunctionRequirementsFailure) {
            throw new \InvalidArgumentException(sprintf('Incompatible result type: %s', get_class($result)));
        }

        $output = '';
        if (count($bannedFunctions = $result->getBannedFunctions())) {
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

        if (count($missingFunctions = $result->getMissingFunctions())) {
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
