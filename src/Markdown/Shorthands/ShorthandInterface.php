<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Shorthands;

use League\CommonMark\Node\Node;

interface ShorthandInterface
{
    /**
     * When running via the CLI
     *
     * @param array<string> $callArgs
     * @return array<Node>
     */
    public function cli(array $callArgs): array;

    /**
     * When running via Cloud
     *
     * @param array<string> $callArgs
     * @return array<Node>
     */
    public function cloud(array $callArgs): array;
}
