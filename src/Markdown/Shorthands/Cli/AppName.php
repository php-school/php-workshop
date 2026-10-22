<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Shorthands\Cli;

use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use League\CommonMark\Node\Inline\Text;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\ShorthandInterface;

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

    public function getCode(): string
    {
        return 'appname';
    }
}
