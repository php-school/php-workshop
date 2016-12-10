<?php

namespace PhpSchool\PhpWorkshop\ResultRenderer;

use Colors\Color;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use PhpSchool\CliMenu\Util\StringUtil;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\Factory\ResultRendererFactory;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Result\ResultGroupInterface;
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
     * @param ResultRendererFactory $resultRendererFactory
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
                || ($result instanceof ResultGroupInterface && $result->isSuccessful())
            ) {
                $successes[] = sprintf(' ✔ Check: %s', $result->getCheckName());
            } else {
                $failures[] = [$result, sprintf(' ✗ Check: %s', $result->getCheckName())];
            }
        }

        $output->emptyLine();
        $output->writeLine($this->center($this->style('*** RESULTS ***', ['magenta', 'bold'])));
        $output->emptyLine();

        $messages = array_merge($successes, array_column($failures, 1));
        $longest = max(array_map('mb_strlen', $messages)) + 4;

        foreach ($successes as $success) {
            $output->writeLine($this->center($this->style(str_repeat(' ', $longest), ['bg_green'])));
            $output->writeLine(
                $this->center($this->style(mb_str_pad($success, $longest), ['bg_green', 'white', 'bold']))
            );
            $output->writeLine($this->center($this->style(str_repeat(' ', $longest), ['bg_green'])));
            $output->emptyLine();
        }

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
        foreach ($failures as list ($failure, $message)) {
            $output->writeLine($this->center($this->style(str_repeat(' ', $padLength), ['bg_red'])));
            $output->writeLine($this->center($this->style(\mb_str_pad($message, $padLength), ['bg_red'])));
            $output->writeLine($this->center($this->style(str_repeat(' ', $padLength), ['bg_red'])));

            $output->emptyLine();
            $output->write($this->renderResult($failure));
        }

        $output->lineBreak();
        $output->emptyLine();
        $output->emptyLine();
        $this->fullWidthBlock($output, 'Your solution was unsuccessful!', ['white', 'bg_red', 'bold']);
        $output->emptyLine();

        $output->writeLine(
            $this->center(sprintf(" Your solution to %s didn't pass. Try again!", $exercise->getName()))
        );
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
        $output->lineBreak();
        $output->emptyLine();
        $output->emptyLine();
        $this->fullWidthBlock($output, 'PASS!', ['white', 'bg_green', 'bold']);
        $output->emptyLine();

        if ($exercise instanceof ProvidesSolution) {
            $output->writeLine($this->center("Here's the official solution in case you want to compare notes:"));
            $output->emptyLine();
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

        $output->emptyLine();
        $output->writeLine($this->center(sprintf('You have %d challenges left.', $numExercises - $completedCount)));
        $output->writeLine($this->center(sprintf('Type "%s" and hit enter to show the menu.', $this->appName)));
        $output->emptyLine();
    }

    /**
     * @param string $string
     * @return string string
     */
    public function center($string)
    {
        $stringHalfLength = mb_strlen(StringUtil::stripAnsiEscapeSequence($string)) / 2;
        $widthHalfLength  = ceil($this->terminal->getWidth() / 2);
        $start            = $widthHalfLength - $stringHalfLength;

        if ($start < 0) {
            $start = 0;
        }

        return str_repeat(' ', $start) . $string;
    }

    /**
     * @param OutputInterface $output
     * @param $string
     * @param array $style
     */
    private function fullWidthBlock(OutputInterface $output, $string, array $style)
    {
        $stringLength     = mb_strlen(StringUtil::stripAnsiEscapeSequence($string));
        $stringHalfLength = $stringLength / 2;
        $widthHalfLength  = ceil($this->terminal->getWidth() / 2);
        $start            = ceil($widthHalfLength - $stringHalfLength);

        $output->writeLine($this->style(str_repeat(' ', $this->terminal->getWidth()), $style));
        $output->writeLine(
            $this->style(
                sprintf(
                    '%s%s%s',
                    str_repeat(' ', $start),
                    $string,
                    str_repeat(' ', $this->terminal->getWidth() - $stringLength - $start)
                ),
                $style
            )
        );
        $output->writeLine($this->style(str_repeat(' ', $this->terminal->getWidth()), $style));
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
        return $this->style(str_repeat('─', $this->terminal->getWidth()), 'yellow');
    }
}
