<?php

namespace PhpSchool\PhpWorkshop\Markdown;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;

class Context
{
    public const CONTEXT_CLI = 'cli';
    public const CONTEXT_CLOUD = 'cloud';

    /**
     * @var string
     */
    private $context;

    public function __construct(string $context)
    {
        if (!in_array($context, [self::CONTEXT_CLI, self::CONTEXT_CLOUD], true)) {
            throw InvalidArgumentException::notValidParameter(
                'context',
                [self::CONTEXT_CLI, self::CONTEXT_CLOUD],
                $context
            );
        }
        $this->context = $context;
    }

    public function getCurrentContext(): string
    {
        return $this->context;
    }
}