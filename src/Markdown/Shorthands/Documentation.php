<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Shorthands;

use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Inline\Newline;
use League\CommonMark\Node\Inline\Text;

final class Documentation implements ShorthandInterface
{
    public function __invoke(array $callArgs): array
    {
        if (\count($callArgs) < 3) {
            return [];
        }

        $callout = array_shift($callArgs);
        $lang = array_shift($callArgs);
        $url = "https://php.net/manual/{$lang}/";

        $links = array_map(function (string $link) use ($url) {
            $link = $url . $link;
            return new Link($link, $link, $link);
        }, $callArgs);

        return array_merge(
            [
                new Text('Documentation on '),
                new Code($callout),
                new Text(' can be found by pointing your browser here:'),
                new Newline(),
            ],
            $links,
        );
    }

    public function getCode(): string
    {
        return 'doc';
    }
}
