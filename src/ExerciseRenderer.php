<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop;

use Colors\Color;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\PhpWorkshop\Output\OutputInterface;

/**
 * This class is used to render the exercise problem to the student, it also sets the current exercise
 * on to the user state object.
 */
class ExerciseRenderer
{
    /**
     * @var string
     */
    private $appName;

    /**
     * @var ExerciseRepository
     */
    private $exerciseRepository;

    /**
     * @var MarkdownRenderer
     */
    private $markdownRenderer;

    /**
     * @var Color
     */
    private $color;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var UserState
     */
    private $userState;

    /**
     * @var UserStateSerializer
     */
    private $userStateSerializer;

    /**
     * @param string $appName
     * @param ExerciseRepository $exerciseRepository
     * @param UserState $userState
     * @param UserStateSerializer $userStateSerializer
     * @param MarkdownRenderer $markdownRenderer
     * @param Color $color
     * @param OutputInterface $output
     */
    public function __construct(
        $appName,
        ExerciseRepository $exerciseRepository,
        UserState $userState,
        UserStateSerializer $userStateSerializer,
        MarkdownRenderer $markdownRenderer,
        Color $color,
        OutputInterface $output
    ) {
        $this->appName = $appName;
        $this->exerciseRepository = $exerciseRepository;
        $this->markdownRenderer = $markdownRenderer;
        $this->color = $color;
        $this->output = $output;
        $this->userState = $userState;
        $this->userStateSerializer = $userStateSerializer;
    }

    /**
     * @param CliMenu $menu
     */
    public function __invoke(CliMenu $menu): void
    {
        $menu->close();

        $item = $menu->getSelectedItem();
        $exercise = $this->exerciseRepository->findByName($item->getText());
        $exercises = $this->exerciseRepository->findAll();

        $this->userState->setCurrentExercise($item->getText());
        $this->userStateSerializer->serialize($this->userState);

        $numExercises = count($exercises);
        $exerciseIndex = ((int) array_search($exercise, $exercises)) + 1;

        $output  = "\n";
        $output .= $this->color->__invoke(' LEARN YOU THE PHP FOR MUCH WIN! ')->magenta()->bold() . "\n";
        $output .= $this->color->__invoke('*********************************')->magenta()->bold() . "\n";
        $output .= "\n";
        $output .= $this->color->__invoke(" " . $exercise->getName())->yellow()->bold() . "\n";
        $output .= $this->color->__invoke(sprintf(" Exercise %d of %d\n\n", $exerciseIndex, $numExercises))->yellow();

        $content = (string) file_get_contents($exercise->getProblem());
        $doc     = $this->markdownRenderer->render($content);
        $doc     = str_replace('{appname}', $this->appName, $doc);
        $output .= $doc;

        $output .= "\n";
        $output .= $this->helpLine('To print these instructions again, run', 'print');
        $output .= $this->helpLine('To execute your program in a test environment, run', 'run program.php');
        $output .= $this->helpLine('To verify your program, run', 'verify program.php');
        $output .= $this->helpLine('For help run', 'help');
        $output .= "\n\n";

        $this->output->write($output);
    }

    /**
     * @param string $text
     * @param string $cmd
     * @return string
     */
    private function helpLine(string $text, string $cmd): string
    {
        $cmd = $this->color->__invoke(sprintf('%s %s', $this->appName, $cmd))->yellow()->__toString();
        return sprintf(
            " %s %s: %s\n",
            $this->color->__invoke("»")->bold()->__toString(),
            $text,
            $cmd
        );
    }
}
