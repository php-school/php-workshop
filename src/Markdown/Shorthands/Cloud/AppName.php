<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Shorthands\Cloud;

use League\CommonMark\Inline\Element\Code;
use League\CommonMark\Inline\Element\Emphasis;
use League\CommonMark\Inline\Element\Strong;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Node\Node;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\ShorthandInterface;

final class AppName implements ShorthandInterface
{
    /**
     * @param array<string> $callArgs
     * @return array<Node>
     */
    public function __invoke(array $callArgs): array
    {
        return [];
    }
}
