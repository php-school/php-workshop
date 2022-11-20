<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Shorthands;

use League\CommonMark\Inline\Element\Code;
use League\CommonMark\Inline\Element\Emphasis;
use League\CommonMark\Inline\Element\Strong;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Node\Node;

final class AppName implements ShorthandInterface
{
    /**
     * @var string
     */
    private $appName;

    public function __construct(string $appName)
    {
        $this->appName = $appName;
    }

    /**
     * @param array<string> $callArgs
     * @return array<Node>
     */
    public function __invoke(array $callArgs): array
    {
        $wrapped = isset($callArgs[0]);

        if (false === $wrapped) {
            return [new Text($this->appName)];
        }

        switch ($callArgs[0]) {
            case '`':
                return [new Code($this->appName)];
            case '*':
                $text = new Text($this->appName);
                $container = new Strong();
                $container->appendChild($text);
                return [$container];
            case '_':
                $text = new Text($this->appName);
                $container = new Emphasis();
                $container->appendChild($text);
                return [$container];
        }

        return [];
    }
}
