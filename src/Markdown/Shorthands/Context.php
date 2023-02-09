<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Shorthands;

use League\CommonMark\Inline\Element\Text;
use PhpSchool\PhpWorkshop\Markdown\CurrentContext;

final class Context implements ShorthandInterface
{
    /**
     * @var CurrentContext
     */
    private $currentContext;

    public function __construct(CurrentContext $currentContext)
    {
        $this->currentContext = $currentContext;
    }

    public function __invoke(array $callArgs): array
    {
        $offset = array_search($this->currentContext->get(), $callArgs, true);

        if (false === $offset || !is_int($offset) || !isset($callArgs[$offset + 1])) {
            return [];
        }

        return [new Text($callArgs[$offset + 1])];
    }

    public function getCode(): string
    {
        return 'context';
    }
}
