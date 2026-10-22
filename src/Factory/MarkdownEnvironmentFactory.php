<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Factory;

use League\CommonMark\Environment\Environment;
use PhpSchool\CliMdRenderer\CliExtension;
use PhpSchool\PhpWorkshop\Markdown\ProblemFileExtension;
use PhpSchool\PhpWorkshop\Markdown\Renderer\ContextSpecificRenderer;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\ShorthandInterface;

/**
 * Create the commonmark environment used to parse and render problem files
 */
class MarkdownEnvironmentFactory
{
    /**
     * @param array<string, ShorthandInterface> $shorthands
     */
    public function create(int $width, ContextSpecificRenderer $contextSpecificRenderer, array $shorthands): Environment
    {
        $environment = new Environment([
            'renderer' => [
                'width' => $width,
            ],
        ]);

        $environment
            ->addExtension(new CliExtension())
            ->addExtension(new ProblemFileExtension($contextSpecificRenderer, $shorthands));

        return $environment;
    }
}
