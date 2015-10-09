<?php

namespace PhpWorkshop\PhpWorkshop\ResultRenderer;

use Colors\Color;
use MikeyMike\CliMenu\Terminal\TerminalInterface;
use PhpSchool\PSX\Factory;
use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\ExerciseRepository;
use PhpWorkshop\PhpWorkshop\Result\ResultInterface;
use PhpWorkshop\PhpWorkshop\Result\StdOutFailure;
use PhpWorkshop\PhpWorkshop\Result\Success;
use PhpWorkshop\PhpWorkshop\ResultAggregator;
use PhpWorkshop\PhpWorkshop\UserState;

/**
 * Class ResultsRenderer
 * @package PhpWorkshop\PhpWorkshop\ResultRenderer
 */
class ResultsRenderer
{

    private $color;
    private $exerciseRepository;
    private $terminal;

    /**
     * @var ResultRendererInterface[]
     */
    private $renderers = [];

    public function __construct(Color $color, TerminalInterface $terminal, ExerciseRepository $exerciseRepository)
    {
        $this->color = $color;
        $this->terminal = $terminal;
        $this->exerciseRepository = $exerciseRepository;}

    public function registerRenderer($resultClass, ResultRendererInterface $renderer)
    {
        $this->renderers[$resultClass] = $renderer;
    }

    /**
     * @param ResultAggregator $results
     * @param ExerciseInterface $exercise
     * @return string
     */
    public function render(ResultAggregator $results, ExerciseInterface $exercise, UserState $userState)
    {
        if ($results->isSuccessful()) {
            return $this->renderSuccess($results, $exercise, $userState);
        }

        $successes = [];
        foreach ($results as $result) {
            if ($result instanceof Success) {
                $successes[] = sprintf(' ✔ Check: %s', $result->getCheckName());
            }
        }

        $strings = array_map('strlen', $successes);

        $failures = [];
        foreach ($results as $result) {
            if ($result instanceof Success) {
                continue;
            }

            $string = ' ' . $this->color->__invoke(sprintf(' ✗ Submission results did not match expected! '))->bg('red')->fg('white')->__toString();
            $strings[] = strlen($string);
            $failures[] = $string;
            $failures[] = $this->getRenderer($result)->render($result);
        }
        var_dump($strings);
        $longest = max($strings) + 2;
        $successes = array_map(function ($line) use ($longest) {
            return str_pad($line, $longest);
        }, $successes);
        $failures = array_map(function ($line) use ($longest) {
            return str_pad($line, $longest);
        }, $failures);

        $successes = array_map(function ($line) {
            return sprintf(' %s', $this->color->__invoke($line)->bg('green')->fg('black')->__toString());
        }, $successes);

        $lines = array_merge($successes, $failures);

        $lines[] = $this->color->__invoke(" FAIL!")->fg('red')->bg('default')->bold()->__toString();
        $lines[] = '';

        $lines[] = 'Your solution to HELLO WORLD didn\'t pass. Try again!';
        $lines[] = '';
        $lines[] = '';

        return implode("\n", $lines);
    }

    private function renderSuccess(ResultAggregator $results, ExerciseInterface $exercise, UserState $userState)
    {
        $lines = [];
        foreach ($results as $result) {
            $lines[] = sprintf(' ✔ Check: %s', $result->getCheckName());
        }

        $longest = max(array_map('strlen', $lines)) + 2;
        $lines = array_map(function ($line) use ($longest) {
            return str_pad($line, $longest);
        }, $lines);

        $lines = array_map(function ($line) {
            return sprintf(' %s', $this->color->__invoke($line)->bg('green')->fg('black')->__toString());
        }, $lines);

        $lines[] = '';
        $lines[] = $this->color->__invoke(" PASS!")->fg('green')->bg('default')->bold()->__toString();
        $lines[] = '';

        $lines[] = 'Here\'s the official solution in case you want to compare notes:';
        $lines[] = $this->color->__invoke(str_repeat("─", $this->terminal->getWidth()))->fg('yellow')->__toString();

        $syntaxHighlighter = (new Factory)->__invoke();
        $code = explode("\n", $syntaxHighlighter->highlight(file_get_contents($exercise->getSolution())));
        $lines = array_merge($lines, $code);
        $lines[] = $this->color->__invoke(str_repeat("─", $this->terminal->getWidth()))->fg('yellow')->__toString();

        $completedCount = count($userState->getCompletedExercises());
        $numExercises = $this->exerciseRepository->count();


        $lines[] = sprintf('You have %d challenges left.', $numExercises - $completedCount);
        $lines[] = sprintf('Type "%s" to show the menu.', $_SERVER['argv'][0]);
        $lines[] = '';
        return implode("\n", $lines) . "\n";
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
