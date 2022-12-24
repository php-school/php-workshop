<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\ComposerFailure;

/**
 * Renderer for `PhpSchool\PhpWorkshop\Result\ComposerFailure`.
 */
class ComposerFailureRenderer implements ResultRendererInterface
{
    /**
     * @var ComposerFailure
     */
    private $result;

    /**
     * @param ComposerFailure $result The failure.
     */
    public function __construct(ComposerFailure $result)
    {
        $this->result = $result;
    }

    /**
     * Print a list of the missing components and packages.
     *
     * @param ResultsRenderer $renderer
     * @return string
     */
    public function render(ResultsRenderer $renderer): string
    {
        if ($this->result->isMissingComponent()) {
            /** @var string $component */
            $component = $this->result->getMissingComponent();

            $type = str_contains($component, '.') ?  'file' : 'folder';

            return $renderer->center("No $component $type found") . "\n";
        }

        if ($this->result->isMissingPackages()) {
            $missingPackages = $this->result->getMissingPackages();

            return $renderer->center(sprintf(
                "Lockfile doesn't include the following packages at any version: \"%s\"\n",
                implode('", "', $missingPackages)
            ));
        }

        return '';
    }
}
