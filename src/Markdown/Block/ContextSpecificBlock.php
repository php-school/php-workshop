<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Block;

use League\CommonMark\ContextInterface;
use League\CommonMark\Cursor;
use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\AbstractStringContainerBlock;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Markdown\Parser\ContextSpecificBlockParser;

final class ContextSpecificBlock extends AbstractStringContainerBlock
{
    /**
     * @var string
     */
    private $type;

    public function __construct(string $type = ContextSpecificBlockParser::CLI_TYPE)
    {
        if (!in_array($type, ContextSpecificBlockParser::TYPES, true)) {
            throw InvalidArgumentException::notValidParameter('type', ContextSpecificBlockParser::TYPES, $type);
        }

        $this->type = $type;

        parent::__construct();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function canContain(AbstractBlock $block): bool
    {
        return false;
    }

    public function isCode(): bool
    {
        return true;
    }

    public function matchesNextLine(Cursor $cursor): bool
    {
        return true;
    }

    public function finalize(ContextInterface $context, int $endLineNumber)
    {
        parent::finalize($context, $endLineNumber);

        $this->finalStringContents = \implode("\n", $this->strings->toArray());
    }

    public function handleRemainingContents(ContextInterface $context, Cursor $cursor)
    {
        $tagged = $cursor->match(ContextSpecificBlockParser::getParserRegex());
        if ($tagged !== null) {
            $this->finalize($context, $context->getLineNumber());
            return;
        }
        
        /** @var self $tip */
        $tip = $context->getTip();
        $tip->addLine($cursor->getRemainder());
    }
}
