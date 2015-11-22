<?php

namespace PhpSchool\PhpWorkshop;

use PhpParser\Error;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpSchool\PhpWorkshop\Exercise\AstIntrospectable;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\PreProcessable;
use PhpSchool\PhpWorkshop\Exercise\SubmissionPatchable;
use PhpSchool\PhpWorkshop\Exercise\SubmissionPatcher;

/**
 * Class CodePatcher
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CodePatcher
{
    /**
     * @var Parser
     */
    private $parser;
    
    /**
     * @var Standard
     */
    private $printer;
    
    /**
     * @var Patch
     */
    private $defaultPatch;

    /**
     * @param Parser $parser
     * @param Standard $printer
     * @param Patch $defaultPatch
     */
    public function __construct(Parser $parser, Standard $printer, Patch $defaultPatch = null)
    {
        $this->parser       = $parser;
        $this->printer      = $printer;
        $this->defaultPatch = $defaultPatch;
    }
    
    /**
     * @param ExerciseInterface $exercise
     * @param string $code
     * @return string
     */
    public function patch(ExerciseInterface $exercise, $code)
    {
        if (null !== $this->defaultPatch) {
            $code = $this->applyPatch($this->defaultPatch, $code);
        }

        if ($exercise instanceof SubmissionPatchable) {
            $code = $this->applyPatch($exercise->getPatch(), $code);
        }
        
        return $code;
    }

    /**
     * @param Patch $patch
     * @param string $code
     * @return string
     */
    private function applyPatch(Patch $patch, $code)
    {
        $statements = $this->parser->parse($code);
        foreach ($patch->getInsertions() as $insertion) {
            try {
                $codeToInsert = $insertion->getCode();
                $codeToInsert = sprintf('<?php %s', preg_replace('/^\s*<\?php/', '', $codeToInsert));
                $additionalStatements = $this->parser->parse($codeToInsert);
            } catch (Error $e) {
                //we should probably log this and have a dev mode or something
                continue;
            }

            switch ($insertion->getType()) {
                case CodeInsertion::TYPE_BEFORE:
                    array_unshift($statements, ...$additionalStatements);
                    break;
                case CodeInsertion::TYPE_AFTER:
                    array_push($statements, ...$additionalStatements);
                    break;
            }
        }

        foreach ($patch->getTransformers() as $transformer) {
            $statements = $transformer($statements);
        }

        return $this->printer->prettyPrintFile($statements);
    }
}
