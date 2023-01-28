<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Shorthands;

use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Node\Node;

final class Run implements ShorthandInterface
{
    /**
     * @param array<string> $callArgs
     * @return Text[]
     */
    public function __invoke(array $callArgs): array
    {
        return [
            new Text('Run XXXX '),
        ];
    }

    public function cli(array $callArgs): array
    {
        // TODO: Implement cli() method.
    }

    public function cloud(array $callArgs): array
    {
        // TODO: Implement cloud() method.
    }
}
