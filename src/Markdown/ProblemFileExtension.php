<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown;

use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\Extension\ExtensionInterface;
use PhpSchool\PhpWorkshop\Markdown\Block\ContextSpecificBlock;
use PhpSchool\PhpWorkshop\Markdown\Renderer\ContextSpecificRendererInterface;
use PhpSchool\PhpWorkshop\Markdown\Parser\ContextSpecificBlockParser;
use PhpSchool\PhpWorkshop\Markdown\Parser\HandleBarParser;
use PhpSchool\PhpWorkshop\Markdown\Renderer\ContextSpecificRenderer;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\ShorthandInterface;

final class ProblemFileExtension implements ExtensionInterface
{
    /**
     * @var ContextSpecificRendererInterface
     */
    private $contextSpecificRenderer;

    /**
     * @var array<string, ShorthandInterface>
     */
    private $shorthandExpanders;

    public function __construct(
        ContextSpecificRendererInterface $contextSpecificRenderer,
        array $shorthandExpanders
    )
    {
        $this->contextSpecificRenderer = $contextSpecificRenderer;
        $this->shorthandExpanders = $shorthandExpanders;
    }

    public function register(ConfigurableEnvironmentInterface $environment): void
    {
        $environment
            ->addBlockParser(new ContextSpecificBlockParser())
            ->addInlineParser(new HandleBarParser($this->shorthandExpanders))
            ->addBlockRenderer(ContextSpecificBlock::class, $this->contextSpecificRenderer);
    }
}
