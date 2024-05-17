<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop;

use PhpParser\Error;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\SubmissionPatchable;
use PhpSchool\PhpWorkshop\Patch\Transformer;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

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
     * @param LoggerInterface $logger
     * @param Patch|null $defaultPatch
     */
    public function __construct(Parser $parser, Standard $printer, LoggerInterface $logger, Patch $defaultPatch = null)
    {
        $this->parser = $parser;
        $this->printer = $printer;
        $this->logger = $logger;
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

        foreach ($patch->getModifiers() as $modifier) {
            $declare = null;
            if ($this->isFirstStatementStrictTypesDeclare($statements)) {
                $declare = array_shift($statements);
            }

            if ($modifier instanceof CodeInsertion) {
                $statements = $this->applyCodeInsertion($modifier, $statements);
            }

            if ($modifier instanceof \Closure) {
                $statements = $modifier($statements);
            }

            if ($modifier instanceof Transformer) {
                $statements = $modifier->transform($statements);
            }

            if ($declare !== null && !$this->isFirstStatementStrictTypesDeclare($statements)) {
                array_unshift($statements, $declare);
            }
        }

        return $this->printer->prettyPrintFile($statements);
    }

    /**
     * @param array<Stmt> $statements
     */
    public function isFirstStatementStrictTypesDeclare(array $statements): bool
    {
        return isset($statements[0])
            && $statements[0] instanceof Declare_
            && isset($statements[0]->declares[0])
            && $statements[0]->declares[0]->key->name === 'strict_types';
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
            $this->logger->critical(
                'Code Insertion could not be parsed: ' . $e->getMessage(),
                ['code' => $codeInsertion->getCode()],
            );
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
