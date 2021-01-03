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
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

class CodeExistsCheck implements SimpleCheckInterface
{
    /**
     * @var Parser
     */
    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function getName(): string
    {
        return 'Code Exists Check';
    }

    /**
     * Check solution provided contains code
     * Note: We don't care if it's valid code at this point
     */
    public function check(ExerciseInterface $exercise, Input $input): ResultInterface
    {
        $noopHandler = new class implements ErrorHandler {
            public function handleError(Error $error): void
            {
            }
        };

        $code = (string) file_get_contents($input->getRequiredArgument('program'));
        $statements = $this->parser->parse($code, $noopHandler);

        $empty = null === $statements || empty($statements);

        if (!$empty) {
            $openingTag = is_array($statements) && count($statements) === 1 ? $statements[0] : null;
            $empty = $openingTag instanceof InlineHTML ? in_array($openingTag->value, ['<?php', '<?']) : false;
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
