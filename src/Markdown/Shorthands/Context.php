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

    public function cli(array $callArgs): array
    {
        return $this->getBlocks($callArgs);
    }

    public function cloud(array $callArgs): array
    {
        return $this->getBlocks($callArgs);
    }

    /**
     * @param array<string> $callArgs
     * @return Text[]
     */
    public function getBlocks(array $callArgs): array
    {
        $offset = array_search($this->type, $callArgs, true);

        if (false === $offset || !is_int($offset) || !isset($callArgs[$offset + 1])) {
            return [];
        }

        return [new Text($callArgs[$offset + 1])];
    }
}
