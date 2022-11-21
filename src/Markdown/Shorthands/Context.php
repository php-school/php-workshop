<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Shorthands;

use League\CommonMark\Inline\Element\Text;

final class Context implements ShorthandInterface
{
    /**
     * @var string
     */
    private $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @param array<string> $callArgs
     * @return Text[]
     */
    public function __invoke(array $callArgs): array
    {
        $offset = array_search($this->type, $callArgs, true);

        if (false === $offset || !is_int($offset) || !isset($callArgs[$offset + 1])) {
            return [];
        }

        return [new Text($callArgs[$offset + 1])];
    }
}
