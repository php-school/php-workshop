<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Shorthands;

use League\CommonMark\Node\Node;

interface ShorthandInterface
{
    /**
     * @param array<string> $callArgs
     * @return array<Node>
     */
    public function __invoke(array $callArgs): array;
}
