<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Shorthands;

use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Node\Node;
use PhpSchool\PhpWorkshop\Exception\RuntimeException;

final class Verify implements ShorthandInterface
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
     * @return Text[]
     */
    public function __invoke(array $callArgs): array
    {
        if (!isset($callArgs[1])) {
            throw new RuntimeException('The solution file must be specific');
        }

        return [
            new Text($this->appName . ' verify ' . $callArgs[1]),
        ];
    }

    public function cli(array $callArgs): array
    {
        if (!isset($callArgs[1])) {
            throw new RuntimeException('The solution file must be specific');
        }

        return [
            new Text($this->appName . ' verify ' . $callArgs[1]),
        ];
    }

    public function cloud(array $callArgs): array
    {
        return [
            new Text('Click the Verify button in the bottom right'),
        ];
    }
}
