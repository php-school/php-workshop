<?php

namespace PhpWorkshop\PhpWorkshop;

use Colors\Color;
use MikeyMike\CliMenu\CliMenu;

/**
 * Class ExerciseRenderer
 * @package PhpWorkshop\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseRenderer
{
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
     * @var Output
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
     * @param ExerciseRepository $exerciseRepository
     * @param UserState $userState
     * @param UserStateSerializer $userStateSerializer
     * @param MarkdownRenderer $markdownRenderer
     * @param Color $color
     * @param Output $output
     */
    public function __construct(
        ExerciseRepository $exerciseRepository,
        UserState $userState,
        UserStateSerializer $userStateSerializer,
        MarkdownRenderer $markdownRenderer,
        Color $color,
        Output $output
    ) {
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
    public function __invoke(CliMenu $menu)
    {
        $item           = $menu->getSelectedItem();
        $exercise       = $this->exerciseRepository->findByName($item->getText());
        $exercises      = $this->exerciseRepository->findAll();

        $this->userState->setCurrentExercise($item->getText());
        $this->userStateSerializer->serialize($this->userState);

        $numExercises   = count($exercises);
        $exerciseIndex  = array_search($exercise, $exercises) + 1;

        echo "\n";
        echo $this->color->__invoke(' LEARN YOU THE PHP FOR MUCH WIN! ')->green()->bold() . "\n";
        echo $this->color->__invoke('*********************************')->green()->bold() . "\n";
        echo $this->color->__invoke(" " . $exercise->getName())->yellow()->bold() . "\n";
        echo $this->color->__invoke(sprintf(" Exercise %d of %d\n\n", $exerciseIndex, $numExercises))->yellow();

        $content = file_get_contents($exercise->getProblem());
        $doc = $this->markdownRenderer->render($content);
        //todo: get rid of this global
        $doc = str_replace('{appname}', $_SERVER['argv'][0], $doc);
        echo $doc;

        echo "\n";
        echo $this->helpLine('To print these instructions again, run', 'print');
        echo $this->helpLine('To execute your program in a test environment, run', 'run program.php');
        echo $this->helpLine('To verify your program, run', 'verify program.php');
        echo $this->helpLine('For help run', 'help');
        echo "\n\n";

        $menu->close();
    }

    /**
     * @param string $text
     * @param string $cmd
     * @return string
     */
    private function helpLine($text, $cmd)
    {
        //todo: and this one BUT ITS SO EASY
        $cmd = $this->color->__invoke(sprintf('php %s %s', $_SERVER['argv'][0], $cmd))->yellow()->__toString();
        return sprintf(
            " %s %s: %s\n",
            $this->color->__invoke("»")->bold()->__toString(),
            $text,
            $cmd
        );
    }
}
