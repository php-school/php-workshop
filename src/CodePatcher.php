<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop;

use PhpParser\Error;
use PhpParser\Node\Stmt;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\SubmissionPatchable;

/**
 * Service to apply patches to a student's solution. Accepts a default patch via the constructor.
 * Patches are pulled from the exercise (if it implements `SubmissionPatchable`) and applied to the
 * given code.
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
     * @var Patch|null
     */
    private $defaultPatch;

    /**
     * This service requires an instance of `Parser` and `Standard`. These services allow
     * to parse code to an AST and to print code from an AST.
     *
     * The service also accepts a default patch. This allows a workshop to apply a patch
     * to every single student solution. This is used (by default) to modify various ini
     * settings, such as increasing the error reporting level.
     *
     * @param Parser $parser
     * @param Standard $printer
     * @param Patch|null $defaultPatch
     */
    public function __construct(Parser $parser, Standard $printer, Patch $defaultPatch = null)
    {
        $this->parser = $parser;
        $this->printer = $printer;
        $this->defaultPatch = $defaultPatch;
    }

    /**
     * Accepts an exercise and a string containing the students solution to the exercise.
     *
     * If there is a default patch, the students solution is patched with that.
     *
     * If the exercise implements `SubmissionPatchable` then the patch is pulled from it and applied to
     * the students solution.
     *
     * @param ExerciseInterface $exercise
     * @param string $code
     * @return string
     */
    public function patch(ExerciseInterface $exercise, string $code): string
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
    private function applyPatch(Patch $patch, string $code): string
    {
        $statements = $this->parser->parse($code);

        if ($statements === null) {
            $statements = [];
        }

        $declare = null;
        if (isset($statements[0]) && $statements[0] instanceof \PhpParser\Node\Stmt\Declare_) {
            $declare = array_shift($statements);
        }

        foreach ($patch->getModifiers() as $modifier) {
            if ($modifier instanceof CodeInsertion) {
                $statements = $this->applyCodeInsertion($modifier, $statements);
                continue;
            }

            if (is_callable($modifier)) {
                $statements = $modifier($statements);
                continue;
            }
        }

        if ($declare !== null) {
            array_unshift($statements, $declare);
        }

        return $this->printer->prettyPrintFile($statements);
    }

    /**
     * @param CodeInsertion $codeInsertion
     * @param array<Stmt> $statements
     * @return array<Stmt>
     */
    private function applyCodeInsertion(CodeInsertion $codeInsertion, array $statements): array
    {
        try {
            $codeToInsert = $codeInsertion->getCode();
            $codeToInsert = sprintf('<?php %s', preg_replace('/^\s*<\?php/', '', $codeToInsert));
            $additionalStatements = $this->parser->parse($codeToInsert) ?? [];
        } catch (Error $e) {
            //we should probably log this and have a dev mode or something
            return $statements;
        }

        switch ($codeInsertion->getType()) {
            case CodeInsertion::TYPE_BEFORE:
                array_unshift($statements, ...$additionalStatements);
                break;
            case CodeInsertion::TYPE_AFTER:
                array_push($statements, ...$additionalStatements);
                break;
        }

        return $statements;
    }
}
