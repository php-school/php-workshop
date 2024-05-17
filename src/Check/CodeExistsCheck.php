<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Check;

use PhpParser\Error;
use PhpParser\ErrorHandler;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\NodeFinder;
use PhpParser\Parser;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

class CodeExistsCheck implements SimpleCheckInterface
{
    public function __construct(private Parser $parser)
    {
    }

    public function getName(): string
    {
        return 'Code Exists Check';
    }

    /**
     * @param ExecutionContext $context The current execution context, containing the exercise, input and working directories.
     *
     * Check solution provided contains code
     * Note: We don't care if it's valid code at this point
     */
    public function check(ExecutionContext $context): ResultInterface
    {
        $noopHandler = new class implements ErrorHandler {
            public function handleError(Error $error): void
            {
            }
        };

        $code = (string) file_get_contents($context->getEntryPoint());
        $statements = $this->parser->parse($code, $noopHandler);

        $empty = null === $statements || empty($statements);

        if (!$empty) {
            $openingTag = is_array($statements) && count($statements) === 1 ? $statements[0] : null;
            $empty = $openingTag instanceof InlineHTML && in_array($openingTag->value, ['<?php', '<?']);
        }

        if ($empty) {
            return Failure::fromCheckAndReason($this, 'No code was found');
        }

        return Success::fromCheck($this);
    }

    /**
     * This check can run on any exercise type.
     */
    public function canRun(ExerciseType $exerciseType): bool
    {
        return in_array($exerciseType->getValue(), [ExerciseType::CGI, ExerciseType::CLI], true);
    }

    public function getExerciseInterface(): string
    {
        return ExerciseInterface::class;
    }

    /**
     * This check must run before executing the solution because all solutions require code
     */
    public function getPosition(): string
    {
        return SimpleCheckInterface::CHECK_BEFORE;
    }
}
