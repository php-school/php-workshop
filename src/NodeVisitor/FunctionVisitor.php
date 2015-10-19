<?php

namespace PhpSchool\PhpWorkshop\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeVisitorAbstract;

/**
 * Class FunctionVisitor
 * @package PhpSchool\PhpWorkshop\NodeVisitor
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FunctionVisitor extends NodeVisitorAbstract
{
    /**
     * @var array
     */
    private $requiredFunctions;
    /**
     * @var array
     */
    private $bannedFunctions;

    /**
     * @var array
     */
    private $requiredUsages = [];

    /**
     * @var array
     */
    private $bannedUsages = [];

    /**
     * @param array $requiredFunctions
     * @param array $bannedFunctions
     */
    public function __construct(array $requiredFunctions, array $bannedFunctions)
    {
        $this->requiredFunctions    = $requiredFunctions;
        $this->bannedFunctions      = $bannedFunctions;
    }

    /**
     * @param Node $node
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof FuncCall) {
            $name = $node->name->__toString();
            if (in_array($name, $this->requiredFunctions)) {
                $this->requiredUsages[] = $node;
            }

            if (in_array($name, $this->bannedFunctions)) {
                $this->bannedUsages[] = $node;
            }
        }
    }

    /**
     * @return bool
     */
    public function hasUsedBannedFunctions()
    {
        return count($this->bannedUsages) > 0;
    }

    /**
     * @return array
     */
    public function getBannedUsages()
    {
        return $this->bannedUsages;
    }

    /**
     * @return array
     */
    public function getRequiredUsages()
    {
        return $this->requiredUsages;
    }

    /**
     * @return bool
     */
    public function hasMetFunctionRequirements()
    {
        $metRequires = array_filter($this->requiredFunctions, function ($function) {
            foreach ($this->getRequiredUsages() as $usage) {
                if ($usage->name->__toString() === $function) {
                    return true;
                }
            }
            return false;
        });

        return count($metRequires) === count($this->requiredFunctions);
    }

    /**
     * @return array
     */
    public function getMissingRequirements()
    {
        return array_filter($this->requiredFunctions, function ($function) {
            foreach ($this->getRequiredUsages() as $usage) {
                if ($usage->name->__toString() === $function) {
                    return false;
                }
            }
            return true;
        });
    }
}
