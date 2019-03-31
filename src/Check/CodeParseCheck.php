<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpParser\Error;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

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
     *
     * @return string
     */
    public function getName()
    {
        return 'Code Parse Check';
    }

    /**
     * This check grabs the contents of the student's solution and
     * attempts to parse it with `nikic/php-parser`. If any exceptions are thrown
     * by the parser, it is treated as a failure.
     *
     * @param ExerciseInterface $exercise The exercise to check against.
     * @param Input $input The command line arguments passed to the command.
     * @return ResultInterface The result of the check.
     */
    public function check(ExerciseInterface $exercise, Input $input)
    {
        
        $code = file_get_contents($input->getArgument('program'));

        try {
            $this->parser->parse($code);
        } catch (Error $e) {
            return Failure::fromCheckAndCodeParseFailure($this, $e, $input->getArgument('program'));
        }
        
        return Success::fromCheck($this);
    }

    /**
     * This check can run on any exercise type.
     *
     * @param ExerciseType $exerciseType
     * @return bool
     */
    public function canRun(ExerciseType $exerciseType)
    {
        return in_array($exerciseType->getValue(), [ExerciseType::CGI, ExerciseType::CLI]);
    }

    /**
     * @return string
     */
    public function getExerciseInterface()
    {
        return ExerciseInterface::class;
    }

    /**
     * This check should be run before executing the student's solution, as, if it cannot be parsed
     * it probably cannot be executed.
     *
     * @return string
     */
    public function getPosition()
    {
        return SimpleCheckInterface::CHECK_BEFORE;
    }
}
