<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Shorthands\Cli;

use League\CommonMark\Inline\Element\Text;
use PhpSchool\PhpWorkshop\Exception\RuntimeException;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\ShorthandInterface;

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
            throw new RuntimeException('The solution file must be specified');
        }

        return [
            new Text($this->appName . ' verify ' . $callArgs[1]),
        ];
    }
}
