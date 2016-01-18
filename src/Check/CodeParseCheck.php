<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpParser\Error;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * Class CodeParseCheck
 * @package PhpSchool\PhpWorkshop\Check
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
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
     * @return string
     */
    public function getName()
    {
        return 'Code Parse Check';
    }

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return ResultInterface
     */
    public function check(ExerciseInterface $exercise, $fileName)
    {
        
        $code = file_get_contents($fileName);

        try {
            $this->parser->parse($code);
        } catch (Error $e) {
            return Failure::fromCheckAndCodeParseFailure($this, $e, $fileName);
        }
        
        return Success::fromCheck($this);
    }

    /**
     * @param ExerciseType $exerciseType
     * @return bool
     */
    public function canRun(ExerciseType $exerciseType)
    {
        return true;
    }

    /**
     *
     * @return string
     */
    public function getExerciseInterface()
    {
        return ExerciseInterface::class;
    }
}
