<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use Colors\Color;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\Factory\ResultRendererFactory;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Result\SuccessInterface;
use PhpSchool\PSX\SyntaxHighlighter;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\UserState;

/**
 * Renderer which renders a `PhpSchool\PhpWorkshop\ResultAggregator` and writes it the output.
 *
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
     * @var SyntaxHighlighter
     */
    private $syntaxHighlighter;

    /**
     * @var ResultRendererFactory
     */
    private $resultRendererFactory;

    /**
     * @param string $appName The name of the binary to run this workshop.
     * @param Color $color A instance of `Color` used to colour strings with ANSI escape codes.
     * @param TerminalInterface $terminal A helper to get information regarding the current terminal.
     * @param ExerciseRepository $exerciseRepository The exercise repository.
     * @param SyntaxHighlighter $syntaxHighlighter A PHP syntax highlighter for the terminal, uses ANSI escape codes.
     */
    public function __construct(
        $appName,
        Color $color,
        TerminalInterface $terminal,
        ExerciseRepository $exerciseRepository,
        SyntaxHighlighter $syntaxHighlighter,
        ResultRendererFactory $resultRendererFactory
    ) {
        $this->color                 = $color;
        $this->terminal              = $terminal;
        $this->exerciseRepository    = $exerciseRepository;
        $this->syntaxHighlighter     = $syntaxHighlighter;
        $this->appName               = $appName;
        $this->resultRendererFactory = $resultRendererFactory;
    }

    /**
     * Render the result set to the output and statistics on the number of exercises completed and
     * remaining.
     *
     * @param ResultAggregator $results The result set.
     * @param ExerciseInterface $exercise The exercise instance that was just attempted.
     * @param UserState $userState The current state of the student's progress.
     * @param OutputInterface $output The output instance.
     */
    public function render(
        ResultAggregator $results,
        ExerciseInterface $exercise,
        UserState $userState,
        OutputInterface $output
    ) {
        $successes  = [];
        $failures   = [];
        foreach ($results as $result) {
            if ($result instanceof SuccessInterface
                || ($result instanceof ResultAggregator && $result->isSuccessful())
            ) {
                $successes[] = sprintf(' ✔ Check: %s', $result->getCheckName());
            } else {
                $failures[] = [$result, sprintf(' ✗ Check: %s', $result->getCheckName())];
            }
        }
        
        $longest = max(array_map('strlen', array_merge($successes, array_column($failures, 1)))) + 2;
        $output->writeLines(
            $this->padArray($this->styleArray($successes, ['green', 'bg_black', 'bold']), $longest)
        );

        if ($results->isSuccessful()) {
            return $this->renderSuccessInformation($exercise, $userState, $output);
        }
        $this->renderErrorInformation($failures, $longest, $exercise, $output);
    }

    /**
     * @param array $failures
     * @param int $padLength
     * @param ExerciseInterface $exercise
     * @param OutputInterface $output
     */
    private function renderErrorInformation(
        array $failures,
        $padLength,
        ExerciseInterface $exercise,
        OutputInterface $output
    ) {
        foreach ($failures as $result) {
            list ($failure, $message) = $result;
            $output->writeLine(str_pad($this->style($message, ['red', 'bg_black', 'bold']), $padLength));
            $output->write($this->renderResult($failure));
            $output->emptyLine();
        }

        $output->writeLine($this->style(" FAIL!", ['red', 'bg_default', 'bold']));
        $output->emptyLine();

        $output->writeLine(sprintf("Your solution to %s didn't pass. Try again!", $exercise->getName()));
        $output->emptyLine();
        $output->emptyLine();
    }

    /**
     * @param ExerciseInterface $exercise
     * @param UserState $userState
     * @param OutputInterface $output
     */
    private function renderSuccessInformation(
        ExerciseInterface $exercise,
        UserState $userState,
        OutputInterface $output
    ) {
        $output->emptyLine();
        $output->writeLine($this->style(" PASS!", ['green', 'bg_default', 'bold']));
        $output->emptyLine();

        if ($exercise instanceof ProvidesSolution) {
            $output->writeLine("Here's the official solution in case you want to compare notes:");
            $output->writeLine($this->lineBreak());

            foreach ($exercise->getSolution()->getFiles() as $file) {
                $output->writeLine($this->style($file->getRelativePath(), ['bold', 'cyan', 'underline']));
                $output->emptyLine();

                $code = $file->getContents();
                if (pathinfo($file->getRelativePath(), PATHINFO_EXTENSION) === 'php') {
                    $code = $this->syntaxHighlighter->highlight($code);
                }

                //make sure there is a new line at the end
                $code = preg_replace('/\n$/', '', $code) . "\n";

                $output->write($code);
                $output->writeLine($this->lineBreak());
            }
        }

        $completedCount = count($userState->getCompletedExercises());
        $numExercises   = $this->exerciseRepository->count();

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
     * Style/colour a string.
     * Can be any of: black, red, green, yellow, blue, magenta, cyan, white, bold, italic, underline.
     *
     * @param string $string
     * @param array|string $colourOrStyle A single style as a string or multiple styles as an array.
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
     * Render a result. Attempt to find the correct renderer via the result renderer factory.
     *
     * @param ResultInterface $result The result.
     * @return string The string representation of the result.
     */
    public function renderResult(ResultInterface $result)
    {
        return $this->resultRendererFactory->create($result)->render($this);
    }

    /**
     * Draw a line break across the terminal.
     *
     * @return string
     */
    public function lineBreak()
    {
        return $this->style(str_repeat("─", $this->terminal->getWidth()), 'yellow');
    }
}
