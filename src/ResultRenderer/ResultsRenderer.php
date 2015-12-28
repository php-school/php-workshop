<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use Colors\Color;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
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
     * @return string
     */
    public function render(ResultAggregator $results, ExerciseInterface $exercise, UserState $userState)
    {
        if ($results->isSuccessful()) {
            return $this->renderSuccess($results, $exercise, $userState);
        }

        $successes  = [];
        $failures   = [];
        foreach ($results as $result) {
            if ($result instanceof SuccessInterface) {
                $successes[] = $result;
            } else {
                $failures[] = $result;
            }
        }

        $lines = [];
        foreach ($successes as $success) {
            $lines[] = sprintf(' ✔ Check: %s', $success->getCheckName());
        }

        $stringLengths = array_map('strlen', $lines);

        $failuresMessages = [];
        foreach ($failures as $failure) {
            $string             = sprintf(' ✗ Check: %s', $failure->getCheckName());
            $stringLengths[]    = strlen($string);
            $failuresMessages[] = $string;
        }

        $longest            = max($stringLengths) + 2;
        $lines              = $this->padArray($lines, $longest);
        $failuresMessages   = $this->padArray($failuresMessages, $longest);

        $lines              = $this->styleArray($lines, ['green', 'bg_black', 'bold']);
        $failuresMessages   = $this->styleArray($failuresMessages, ['red', 'bg_black', 'bold']);

        foreach ($failures as $key => $result) {
            $lines[] = $failuresMessages[$key];
            $lines[] = $this->getRenderer($result)->render($result, $this);
        }

        $lines[] = $this->color->__invoke(" FAIL!")->fg('red')->bg('default')->bold()->__toString();
        $lines[] = '';

        $lines[] = sprintf("Your solution to %s didn't pass. Try again!", $exercise->getName());
        $lines[] = '';
        $lines[] = '';

        return implode("\n", $lines);
    }

    /**
     * @param ResultAggregator $results
     * @param ExerciseInterface $exercise
     * @param UserState $userState
     * @return string
     */
    private function renderSuccess(ResultAggregator $results, ExerciseInterface $exercise, UserState $userState)
    {
        $lines = [];
        foreach ($results as $result) {
            /** @var Success $result */
            $lines[] = sprintf(' ✔ Check: %s', $result->getCheckName());
        }
        
        $lineBreak = str_repeat("─", $this->terminal->getWidth());

        $lines = $this->padArray($lines, max(array_map('strlen', $lines)) + 2);
        $lines = $this->styleArray($lines, ['green', 'bg_black']);

        $lines[] = '';
        $lines[] = $this->color->__invoke(" PASS!")->fg('green')->bg('default')->bold()->__toString();
        $lines[] = '';

        $lines[] = "Here's the official solution in case you want to compare notes:";
        $lines[] = $this->color->__invoke($lineBreak)->fg('yellow')->__toString();
        
        foreach ($exercise->getSolution()->getFiles() as $file) {
            $code       = $this->syntaxHighlighter->highlight($file->getContents());
            $code       = preg_replace('/<\?php/', sprintf('<?php //%s', $file->getRelativePath()), $code);
            array_push($lines, ...explode("\n", $code));
            $lines[]    = $this->color->__invoke($lineBreak)->fg('yellow')->__toString();
        }
        
        $completedCount = count($userState->getCompletedExercises());
        $numExercises = $this->exerciseRepository->count();
        
        $lines[] = sprintf('You have %d challenges left.', $numExercises - $completedCount);
        $lines[] = sprintf('Type "%s" to show the menu.', $this->appName);
        $lines[] = '';
        return implode("\n", $lines) . "\n";
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
