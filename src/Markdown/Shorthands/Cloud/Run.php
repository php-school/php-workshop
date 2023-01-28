<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Shorthands\Cloud;

use League\CommonMark\Inline\Element\Text;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\ShorthandInterface;

final class Run implements ShorthandInterface
{
    /**
     * @param array<string> $callArgs
     * @return Text[]
     */
    public function __invoke(array $callArgs): array
    {
        return [
            new Text('Click the Run button in the bottom right'),
        ];
    }
}
