<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Asset;

use Colors\Color;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\Delimiter\Processor\EmphasisDelimiterProcessor;
use League\CommonMark\Parser\MarkdownParser;
use PhpSchool\CliMdRenderer\CliExtension;
use PhpSchool\CliMdRenderer\CliRenderer;
use PhpSchool\PhpWorkshop\Markdown\CurrentContext;
use PhpSchool\PhpWorkshop\Markdown\ProblemFileExtension;
use PhpSchool\PhpWorkshop\Markdown\Renderer\ContextSpecificRenderer;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\ShorthandInterface;

/**
 * Builds the commonmark environment, parser and renderer the same way app/config.php does.
 */
final class MarkdownFactory
{
    /**
     * @param array<ShorthandInterface> $shorthands
     */
    public static function environment(CurrentContext $context, array $shorthands = []): Environment
    {
        $environment = new Environment(['renderer' => ['width' => 80]]);
        $environment
            ->addExtension(new CliExtension())
            ->addDelimiterProcessor(new EmphasisDelimiterProcessor('*'))
            ->addDelimiterProcessor(new EmphasisDelimiterProcessor('_'))
            ->addExtension(new ProblemFileExtension(new ContextSpecificRenderer($context), $shorthands));

        return $environment;
    }

    /**
     * @param array<ShorthandInterface> $shorthands
     */
    public static function parser(CurrentContext $context, array $shorthands = []): MarkdownParser
    {
        return new MarkdownParser(self::environment($context, $shorthands));
    }

    /**
     * @param array<ShorthandInterface> $shorthands
     */
    public static function renderer(CurrentContext $context, array $shorthands = []): CliRenderer
    {
        $color = new Color();
        $color->setForceStyle(true);

        return new CliRenderer(self::environment($context, $shorthands), $color);
    }
}
