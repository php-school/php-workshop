<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Check;

use InvalidArgumentException;
use PhpParser\Error;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseCheck\FunctionRequirementsExerciseCheck;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\NodeVisitor\FunctionVisitor;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\FunctionRequirementsFailure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * This check verifies that the student's solution contains usages of some required functions
 * and also does not use certain functions (specified by the exercise).
 */
class FunctionRequirementsCheck implements SimpleCheckInterface
{
    public function __construct(private Parser $parser)
    {
    }

    /**
     * Return the check's name.
     */
    public function getName(): string
    {
        return 'Function Requirements Check';
    }

    /**
     * Parse the students solution and check that there are usages of
     * required functions and that banned functions are not used. The requirements
     * are pulled from the exercise.
     *
     * @param ExecutionContext $context The current execution context, containing the exercise, input and working directories.
     * @return ResultInterface The result of the check.
     */
    public function check(ExecutionContext $context): ResultInterface
    {
        $exercise = $context->getExercise();
        if (!$exercise instanceof FunctionRequirementsExerciseCheck) {
            throw new InvalidArgumentException();
        }

        $requiredFunctions  = $exercise->getRequiredFunctions();
        $bannedFunctions    = $exercise->getBannedFunctions();

        $code = (string) file_get_contents($context->getEntryPoint());

        try {
            $ast = $this->parser->parse($code) ?? [];
        } catch (Error $e) {
            return Failure::fromCheckAndCodeParseFailure($this, $e, $context->getEntryPoint());
        }

        $visitor    = new FunctionVisitor($requiredFunctions, $bannedFunctions);
        $traverser  = new NodeTraverser();
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
            return new FunctionRequirementsFailure($this, $bannedFunctions, $missingFunctions);
        }

        return Success::fromCheck($this);
    }

    /**
     * This check can run on any exercise type.
     *
     * @param ExerciseType $exerciseType
     * @return bool
     */
    public function canRun(ExerciseType $exerciseType): bool
    {
        return in_array($exerciseType->getValue(), [ExerciseType::CGI, ExerciseType::CLI], true);
    }

    public function getExerciseInterface(): string
    {
        return FunctionRequirementsExerciseCheck::class;
    }

    /**
     * This is performed after executing the student's solution because the solution may produce the correct
     * output, but do it in a way that was not correct for the task. This way the student can see the program works
     * but missed some requirements.
     */
    public function getPosition(): string
    {
        return SimpleCheckInterface::CHECK_AFTER;
    }
}
