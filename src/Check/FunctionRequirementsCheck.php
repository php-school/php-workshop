<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpParser\Error;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\FunctionRequirementsExerciseCheck;
use PhpSchool\PhpWorkshop\NodeVisitor\FunctionVisitor;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\FunctionRequirementsFailure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * Class FunctionRequirementsCheck
 * @package PhpSchool\PhpWorkshop\Check
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class FunctionRequirementsCheck implements CheckInterface
{


    /**
     * @var Parser
     */
    private $parser;

    /**
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return ResultInterface
     */
    public function check(ExerciseInterface $exercise, $fileName)
    {
        if (!$exercise instanceof FunctionRequirementsExerciseCheck) {
            throw new \InvalidArgumentException;
        }

        $requiredFunctions  = $exercise->getRequiredFunctions();
        $bannedFunctions    = $exercise->getBannedFunctions();

        $code = file_get_contents($fileName);

        try {
            $ast = $this->parser->parse($code);
        } catch (Error $e) {
            return new Failure(
                'Function Requirements Check',
                sprintf('File: %s could not be parsed. Error: "%s"', $fileName, $e->getMessage())
            );
        }

        $visitor    = new FunctionVisitor($requiredFunctions, $bannedFunctions);
        $traverser  = new NodeTraverser;
        $traverser->addVisitor($visitor);

        $traverser->traverse($ast);

        $bannedFunctions = [];
        if ($visitor->hasUsedBannedFunctions()) {
            $bannedFunctions = array_map(function (FuncCall $node) {
                return ['function' => $node->name->__toString(), 'line' => $node->getLine()];
            }, $visitor->getBannedUsages());
        }

        $missingFunctions = [];
        if (!$visitor->hasMetFunctionRequirements()) {
            $missingFunctions = $visitor->getMissingRequirements();
        }

        if (!empty($bannedFunctions) || !empty($missingFunctions)) {
            return new FunctionRequirementsFailure($bannedFunctions, $missingFunctions);
        }

        return new Success('Function Requirements');
    }

    /**
     * @return bool
     */
    public function breakChainOnFailure()
    {
        return false;
    }
}
