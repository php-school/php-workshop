<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Parser;

use League\CommonMark\Block\Parser\BlockParserInterface;
use PhpSchool\PhpWorkshop\Markdown\Block\ContextSpecificBlock;
use League\CommonMark\ContextInterface;
use League\CommonMark\Cursor;
use PhpSchool\PhpWorkshop\Markdown\CurrentContext;

final class ContextSpecificBlockParser implements BlockParserInterface
{
    public const TYPES = [CurrentContext::CONTEXT_CLI, CurrentContext::CONTEXT_CLOUD];

    public static function getParserRegex(): string
    {
        return '/^{{\s?(' . implode('|', self::TYPES) . ')\s?}}/';
    }

    public function parse(ContextInterface $context, Cursor $cursor): bool
    {
        if ($cursor->getCharacter() !== '{') {
            return false;
        }

        $tagged = $cursor->match(self::getParserRegex());
        if ($tagged === null) {
            return false;
        }

        $type = trim(str_replace(['{', '}'], '', $tagged));

        $context->addBlock(new ContextSpecificBlock($type));

        // TODO: How to handle the entire block?!?

        return true;
    }
}
