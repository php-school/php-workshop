<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Block;

use League\CommonMark\Cursor;
use League\CommonMark\Block\Element\AbstractBlock;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Markdown\Parser\ContextSpecificBlockParser;

final class ContextSpecificBlock extends AbstractBlock
{
    /**
     * @var string
     */
    private $type;

    public function __construct(string $type)
    {
        if (!in_array($type, ContextSpecificBlockParser::TYPES, true)) {
            throw InvalidArgumentException::notValidParameter('type', ContextSpecificBlockParser::TYPES, $type);
        }

        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function canContain(AbstractBlock $block): bool
    {
        return true;
    }

    public function isCode(): bool
    {
        return false;
    }

    public function matchesNextLine(Cursor $cursor): bool
    {
        $content = $cursor->match(ContextSpecificBlockParser::getEndBlockRegex($this->type));
        return $content === null;
    }
}
