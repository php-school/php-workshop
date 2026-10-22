<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Block;

use League\CommonMark\Node\Block\AbstractBlock;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Markdown\Parser\ContextSpecificBlockParser;

final class ContextSpecificBlock extends AbstractBlock
{
    private string $type;

    public function __construct(string $type)
    {
        parent::__construct();

        if (!in_array($type, ContextSpecificBlockParser::TYPES, true)) {
            throw InvalidArgumentException::notValidParameter('type', ContextSpecificBlockParser::TYPES, $type);
        }

        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
