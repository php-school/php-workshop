<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Parser;

use League\CommonMark\Delimiter\Delimiter;
use League\CommonMark\Inline\Element\Code;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\InlineParserContext;
use League\CommonMark\Inline\Parser\InlineParserInterface;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\ShorthandInterface;

final class HandleBarParser implements InlineParserInterface
{
    /**
     * @var array<string, ShorthandInterface>
     */
    private $shorthands;

    /**
     * @param array<string, ShorthandInterface> $shorthands
     */
    public function __construct(array $shorthands)
    {
        $this->shorthands = $shorthands;
    }

    public function getCharacters(): array
    {
        return ['{'];
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        if ($inlineContext->getCursor()->peek(1) !== '{') {
            return false;
        }

        $cursor = $inlineContext->getCursor();

        $handle = $cursor->match('/{{\s?.*?\s?}}/m');

        if (null === $handle) {
            return false;
        }

        $content = trim(str_replace(['{', '}'], '', $handle));
        $parsedArgs = $this->parseArgs($content);
        $shorthand = array_shift($parsedArgs);

        if (null === $shorthand) {
            return false;
        }

        if (!array_key_exists($shorthand, $this->shorthands)) {
            return false;
        }

        $nodes = $this->shorthands[$shorthand]($parsedArgs);

        foreach ($nodes as $node) {
            $inlineContext->getContainer()->appendChild($node);
        }

        return true;
    }

    /**
     * @return array<string>
     */
    private function parseArgs(string $args): array
    {
        $args = preg_split('/\s+/', $args) ?: [];

        $parsedArgs = [];

        $open = false;
        $openChar = '';
        $mergingArgs = [];
        $argCount = count($args);

        for ($i = 0; $i < $argCount; $i++) {
            $firstChar = substr($args[$i], 0, 1);
            $lastChar = substr($args[$i], -1, 1);
            $isAQuoteChar = ($firstChar === '"' || $firstChar === "'") || ($lastChar === '"' || $lastChar === "'");
            $matchesOpenChar = ($firstChar === $openChar) || ($lastChar === $openChar);

            //if this is a quoted arg without spaces inside it
            if (($firstChar === '"' || $firstChar === "'") && $firstChar === $lastChar) {
                $parsedArgs[] = trim($args[$i], $firstChar);
                continue;
            }

            if (!$open && $isAQuoteChar) {
                $open = true;
                $mergingArgs[] = ltrim($args[$i], $firstChar);
                $openChar = $firstChar;
                continue;
            }

            if ($open && !$isAQuoteChar) {
                $mergingArgs[] = $args[$i];
                continue;
            }

            if ($open && $isAQuoteChar && $matchesOpenChar) {
                $mergingArgs[] = rtrim($args[$i], $openChar);
                $parsedArgs[] = implode(' ', $mergingArgs);
                $mergingArgs = [];
                $open = false;
                continue;
            }

            $parsedArgs[] = $args[$i];
        }

        if ($mergingArgs !== []) {
            $parsedArgs = array_merge($parsedArgs, $mergingArgs);
        }

        return $parsedArgs;
    }
}
