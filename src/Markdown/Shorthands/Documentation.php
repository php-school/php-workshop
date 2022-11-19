<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Shorthands;

use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Node\Node;

final class Documentation implements ShorthandInterface
{
    public function __invoke(array $callArgs): array
    {
        $callout = array_shift($callArgs);
        $lang = array_shift($callArgs);
        $url = "https://php.net/manual/{$lang}/";

        $links = array_map(function (string $link) use ($url) {
            $link = $url . $link;
            return new \League\CommonMark\Inline\Element\Link($link, $link, $link);
        }, $callArgs);

        return array_merge(
            [
                new Text('Documentation on '),
                new \League\CommonMark\Inline\Element\Code($callout),
                new Text(' can be found by pointing your browser here:'),
                new \League\CommonMark\Inline\Element\Newline()
            ],
            $links
        );
    }
}
