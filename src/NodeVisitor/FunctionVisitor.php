<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeVisitorAbstract;

/**
 * AST visitor to look for required and missing function requirements
 */
class FunctionVisitor extends NodeVisitorAbstract
{
    /**
     * @var array<string>
     */
    private $requiredFunctions;
    /**
     * @var array<string>
     */
    private $bannedFunctions;

    /**
     * @var array<FuncCall>
     */
    private $requiredUsages = [];

    /**
     * @var array<FuncCall>
     */
    private $bannedUsages = [];

    /**
     * @param array<string> $requiredFunctions
     * @param array<string> $bannedFunctions
     */
    public function __construct(array $requiredFunctions, array $bannedFunctions)
    {
        $this->requiredFunctions = $requiredFunctions;
        $this->bannedFunctions = $bannedFunctions;
    }

    /**
     * @param Node $node
     * @return null
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof FuncCall && $node->name instanceof Node\Name) {
            $name = $node->name->__toString();
            if (in_array($name, $this->requiredFunctions, true)) {
                $this->requiredUsages[] = $node;
            }

            if (in_array($name, $this->bannedFunctions, true)) {
                $this->bannedUsages[] = $node;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasUsedBannedFunctions(): bool
    {
        return count($this->bannedUsages) > 0;
    }

    /**
     * @return array<FuncCall>
     */
    public function getBannedUsages(): array
    {
        return $this->bannedUsages;
    }

    /**
     * @return array<FuncCall>
     */
    public function getRequiredUsages(): array
    {
        return $this->requiredUsages;
    }

    /**
     * @return bool
     */
    public function hasMetFunctionRequirements(): bool
    {
        $metRequires = array_filter($this->requiredFunctions, function ($function) {
            foreach ($this->getRequiredUsages() as $usage) {
                if (!$usage->name instanceof Node\Name) {
                    continue;
                }

                if ($usage->name->__toString() === $function) {
                    return true;
                }
            }
            return false;
        });

        return count($metRequires) === count($this->requiredFunctions);
    }

    /**
     * @return array<string>
     */
    public function getMissingRequirements(): array
    {
        return array_values(array_filter($this->requiredFunctions, function ($function) {
            foreach ($this->getRequiredUsages() as $usage) {
                if (!$usage->name instanceof Node\Name) {
                    continue;
                }

                if ($usage->name->__toString() === $function) {
                    return false;
                }
            }
            return true;
        }));
    }
}
