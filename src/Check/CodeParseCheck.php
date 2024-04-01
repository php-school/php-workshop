<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Check;

use PhpParser\Error;
use PhpParser\Parser;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Environment;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\Utils\Path;

/**
 * This check attempts to parse a student's solution and returns
 * a success or failure based on the result of the parsing.
 */
class CodeParseCheck implements SimpleCheckInterface
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
     * Return the check's name
     */
    public function getName(): string
    {
        return 'Code Parse Check';
    }

    /**
     * This check grabs the contents of the student's solution and
     * attempts to parse it with `nikic/php-parser`. If any exceptions are thrown
     * by the parser, it is treated as a failure.
     *
     * @param ExecutionContext $context The current execution context.
     * @return ResultInterface The result of the check.
     */
    public function check(ExecutionContext $context): ResultInterface
    {
        $code = (string) file_get_contents($context->getStudentSolutionFilePath());

        try {
            $this->parser->parse($code);
        } catch (Error $e) {
            return Failure::fromCheckAndCodeParseFailure($this, $e, $context->getStudentSolutionFilePath());
        }

        return Success::fromCheck($this);
    }

    /**
     * This check can run on any exercise type.
     * @param ExerciseType $exerciseType
     * @return bool
     */
    public function canRun(ExerciseType $exerciseType): bool
    {
        return in_array($exerciseType->getValue(), [ExerciseType::CGI, ExerciseType::CLI], true);
    }

    /**
     * @return string
     */
    public function getExerciseInterface(): string
    {
        return ExerciseInterface::class;
    }

    /**
     * This check should be run before executing the student's solution, as, if it cannot be parsed
     * it probably cannot be executed.
     */
    public function getPosition(): string
    {
        return SimpleCheckInterface::CHECK_BEFORE;
    }
}
