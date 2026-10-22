<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Parser;

use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Markdown\CurrentContext;

final class ContextSpecificBlockParser implements BlockStartParserInterface
{
    public const TYPES = [CurrentContext::CONTEXT_CLI, CurrentContext::CONTEXT_CLOUD];

    public static function getParserRegex(): string
    {
        return '/^{{\s?(' . implode('|', self::TYPES) . ')\s?}}/';
    }

    public static function getEndBlockRegex(string $type): string
    {
        if (!in_array($type, self::TYPES, true)) {
            throw InvalidArgumentException::notValidParameter('type', self::TYPES, $type);
        }

        return '/^{{\s?(' . $type . ')\s?}}/';
    }

    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->getNextNonSpaceCharacter() !== '{') {
            return BlockStart::none();
        }

        $cursor->advanceToNextNonSpaceOrTab();
        $tagged = $cursor->match(self::getParserRegex());

        if ($tagged === null) {
            return BlockStart::none();
        }

        $type = trim(str_replace(['{', '}'], '', $tagged));

        return BlockStart::of(new ContextSpecificBlockContinueParser($type))->at($cursor);
    }
}
