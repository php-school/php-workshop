<?php

namespace PhpWorkshop\PhpWorkshop\Check;

use PhpParser\Error;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\ExerciseCheck\FunctionRequirementsExerciseCheck;
use PhpWorkshop\PhpWorkshop\NodeVisitor\FunctionVisitor;
use PhpWorkshop\PhpWorkshop\Result\Failure;
use PhpWorkshop\PhpWorkshop\Result\ResultInterface;
use PhpWorkshop\PhpWorkshop\Result\Success;

/**
 * Class FunctionRequirementsCheck
 * @package PhpWorkshop\PhpWorkshop\Check
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
            return new Failure(sprintf('File: %s could not be parsed. Error: "%s"', $fileName, $e->getMessage()));
        }

        $visitor    = new FunctionVisitor($requiredFunctions, $bannedFunctions);
        $traverser  = new NodeTraverser;
        $traverser->addVisitor($visitor);

        $traverser->traverse($ast);

        if ($visitor->hasUsedBannedFunctions()) {
            //used some banned functions
            return new Failure(
                'Banned Functions',
                sprintf(
                    'Some functions were used which should not be used in this exercise: %s',
                    implode(
                        '", "',
                        array_map(function (FuncCall $node) {
                            return sprintf('Function: "%s" on line: "%s"', $node->name->__toString(), $node->getLine());
                        }, $visitor->getBannedUsages())
                    )
                )
            );
        }

        if (!$visitor->hasMetFunctionRequirements()) {
            return new Failure(
                'Missing Functions',
                sprintf(
                    'Some function requirements were missing. You should use the functions: "%s"',
                    implode('", "', $visitor->getMissingRequirements())
                )
            );
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
