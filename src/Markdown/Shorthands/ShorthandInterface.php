<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Shorthands;

use League\CommonMark\Node\Node;

interface ShorthandInterface
{
    /**
     * The code the shorthand should react to
     */
    public function getCode(): string;

    /**
     * @param array<string> $callArgs
     * @return array<Node>
     */
    public function __invoke(array $callArgs): array;
}
