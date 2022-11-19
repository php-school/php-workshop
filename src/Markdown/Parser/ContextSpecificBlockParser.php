<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Parser;

use PhpSchool\PhpWorkshop\Markdown\Block\ContextSpecificBlock;
use League\CommonMark\ContextInterface;
use League\CommonMark\Cursor;

final class ContextSpecificBlockParser implements \League\CommonMark\Block\Parser\BlockParserInterface
{
    public const CLI_TYPE = 'cli';
    public const CLOUD_TYPE = 'cloud';
    public const TYPES = [self::CLI_TYPE, self::CLOUD_TYPE];

    public static function getParserRegex(): string
    {
        return '/^{{\s?('. implode('|', self::TYPES) .')\s?}}/';
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
