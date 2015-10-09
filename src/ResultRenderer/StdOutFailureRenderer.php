<?php

namespace PhpWorkshop\PhpWorkshop\ResultRenderer;

use Colors\Color;
use PhpWorkshop\PhpWorkshop\Result\ResultInterface;
use PhpWorkshop\PhpWorkshop\Result\StdOutFailure;

/**
 * Class StdOutFailureRenderer
 * @package PhpWorkshop\PhpWorkshop\ResultRenderer
 */
class StdOutFailureRenderer implements ResultRendererInterface
{

    private $color;

    public function __construct(Color $color)
    {
        $this->color = $color;
    }

    /**
     * @param ResultInterface $result
     * @return string
     */
    public function render(ResultInterface $result)
    {
        if (!$result instanceof StdOutFailure) {
            throw new \InvalidArgumentException(sprintf('Incompatible result type: %s', get_class($result)));
        }

        return sprintf(
            " %s\n%s\n\n  %s\n%s\n",
            $this->style("ACTUAL", ['bold', 'underline', 'yellow']),
            $this->indent($this->style(sprintf('"%s"', $result->getActualOutput()), 'red')),
            $this->style("EXPECTED", ['yellow', 'bold', 'underline']),
            $this->indent($this->style(sprintf('"%s"', $result->getExpectedOutput()), 'red'))
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


    /**
     * @param string $string
     * @param array|string $colourOrStyle
     *
     * @return string
     *
     */
    public function style($string, $colourOrStyle)
    {
        if (is_array($colourOrStyle)) {
            $this->color->__invoke($string);

            while ($style = array_shift($colourOrStyle)) {
                $this->color->apply($style);
            }
            return $this->color->__toString();
        }

        return $this->color->__invoke($string)->apply($colourOrStyle, $string);
    }
}