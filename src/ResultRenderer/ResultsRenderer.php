<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use Colors\Color;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use PhpSchool\PhpWorkshop\Output;
use PhpSchool\PhpWorkshop\Result\SuccessInterface;
use PhpSchool\PSX\SyntaxHighlighter;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\UserState;

/**
 * Class ResultsRenderer
 * @package PhpSchool\PhpWorkshop\ResultRenderer
 */
class ResultsRenderer
{
    /**
     * @var string
     */
    private $appName;

    /**
     * @var Color
     */
    private $color;

    /**
     * @var ExerciseRepository
     */
    private $exerciseRepository;

    /**
     * @var TerminalInterface
     */
    private $terminal;

    /**
     * @var ResultRendererInterface[]
     */
    private $renderers = [];
    
    /**
     * @var SyntaxHighlighter
     */
    private $syntaxHighlighter;

    /**
     * @param $appName
     * @param Color $color
     * @param TerminalInterface $terminal
     * @param ExerciseRepository $exerciseRepository
     * @param SyntaxHighlighter $syntaxHighlighter
     */
    public function __construct(
        $appName,
        Color $color,
        TerminalInterface $terminal,
        ExerciseRepository $exerciseRepository,
        SyntaxHighlighter $syntaxHighlighter
    ) {
        $this->color                = $color;
        $this->terminal             = $terminal;
        $this->exerciseRepository   = $exerciseRepository;
        $this->syntaxHighlighter    = $syntaxHighlighter;
        $this->appName = $appName;
    }

    /**
     * @param $resultClass
     * @param ResultRendererInterface $renderer
     */
    public function registerRenderer($resultClass, ResultRendererInterface $renderer)
    {
        $this->renderers[$resultClass] = $renderer;
    }

    /**
     * @param ResultAggregator $results
     * @param ExerciseInterface $exercise
     * @param UserState $userState
     * @param Output $output
     */
    public function render(ResultAggregator $results, ExerciseInterface $exercise, UserState $userState, Output $output)
    {
        if ($results->isSuccessful()) {
            return $this->renderSuccess($results, $exercise, $userState, $output);
        }

        $successes  = [];
        $failures   = [];
        foreach ($results as $result) {
            if ($result instanceof SuccessInterface) {
                $successes[] = sprintf(' ✔ Check: %s', $result->getCheckName());
            } else {
                $failures[] = [$result, sprintf(' ✗ Check: %s', $result->getCheckName())];
            }
        }
        
        $longest  = max(array_map('strlen', array_merge($successes, array_column($failures, 1)))) + 2;
        $output->writeLines(
            $this->padArray($this->styleArray($successes, ['green', 'bg_black', 'bold']), $longest)
        );

        foreach ($failures as $result) {
            list ($failure, $message) = $result;
            $output->writeLine(str_pad($this->style($message, ['red', 'bg_black', 'bold']), $longest));
            $output->explodeAndWrite($this->getRenderer($failure)->render($failure, $this));
        }

        $output->writeLine($this->style(" FAIL!", ['red', 'bg_default', 'bold']));
        $output->emptyLine();

        $output->writeLine(sprintf("Your solution to %s didn't pass. Try again!", $exercise->getName()));
        $output->emptyLine();
        $output->emptyLine();
    }

    /**
     * @param ResultAggregator $results
     * @param ExerciseInterface $exercise
     * @param UserState $userState
     * @param Output $output
     */
    private function renderSuccess(
        ResultAggregator $results,
        ExerciseInterface $exercise,
        UserState $userState,
        Output $output
    ) {
        $lines = [];
        foreach ($results as $result) {
            /** @var Success $result */
            $lines[] = sprintf(' ✔ Check: %s', $result->getCheckName());
        }
        
        $lineBreak = str_repeat("─", $this->terminal->getWidth());

        $lines = $this->padArray($lines, max(array_map('strlen', $lines)) + 2);
        $lines = $this->styleArray($lines, ['green', 'bg_black']);
        
        $output->writeLines($lines);
        $output->emptyLine();
        $output->writeLine($this->style(" PASS!", ['green', 'bg_default', 'bold']));
        $output->emptyLine();
        
        $output->writeLine("Here's the official solution in case you want to compare notes:");
        $output->writeLine($this->style($lineBreak, 'yellow'));
        
        foreach ($exercise->getSolution()->getFiles() as $file) {
            $code       = $this->syntaxHighlighter->highlight($file->getContents());
            $code       = preg_replace('/<\?php/', sprintf('<?php //%s', $file->getRelativePath()), $code);
            $output->explodeAndWrite($code);
            $output->writeLine($this->style($lineBreak, 'yellow'));
        }
        
        $completedCount = count($userState->getCompletedExercises());
        $numExercises = $this->exerciseRepository->count();

        $output->writeLine(sprintf('You have %d challenges left.', $numExercises - $completedCount));
        $output->writeLine(sprintf('Type "%s" to show the menu.', $this->appName));
        $output->emptyLine();
    }

    /**
     * @param array $lines
     * @param int $length
     * @return array
     */
    private function padArray(array $lines, $length)
    {
        return array_map(function ($line) use ($length) {
            return str_pad($line, $length);
        }, $lines);
    }

    /**
     * @param array $lines
     * @param array $styles
     * @return array
     */
    private function styleArray(array $lines, array $styles)
    {
        return array_map(function ($line) use ($styles) {
            return $this->style($line, $styles);
        }, $lines);
    }

    /**
     * @param string $string
     * @param array|string $colourOrStyle
     *
     * @return string
     *
     */
    public function style($string, $colourOrStyle)
    {
        if (is_array($colourOrStyle)) {
            $this->color->__invoke($string);

            while ($style = array_shift($colourOrStyle)) {
                $this->color->apply($style);
            }
            return $this->color->__toString();
        }

        return $this->color->__invoke($string)->apply($colourOrStyle, $string);
    }

    /**
     * @param ResultInterface $result
     * @return ResultRendererInterface
     */
    private function getRenderer(ResultInterface $result)
    {
        $class = get_class($result);

        if (!isset($this->renderers[$class])) {
            throw new \RuntimeException(sprintf('No renderer found for "%s"', $class));
        }

        return $this->renderers[$class];
    }
}
