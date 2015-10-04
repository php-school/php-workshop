<?php

namespace PhpWorkshop\PhpWorkshop;

use MikeyMike\CliMenu\CliMenu;
use MikeyMike\CliMenu\MenuItem\MenuItem;
use MikeyMike\CliMenu\MenuItem\SelectableItem;
use MikeyMike\CliMenu\MenuStyle;

/**
 * Class Menu
 * @package PhpWorkshop\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Menu
{
    /**
     * @var \MikeyMike\CliMenu\CliMenu
     */
    private $mainMenu;

    /**
     * @var \MikeyMike\CliMenu\CliMenu
     */
    private $optionsMenu;

    /**
     * @param ExerciseRepository $exerciseRepository
     * @param ExerciseRenderer $exerciseRenderer
     */
    public function __construct(ExerciseRepository $exerciseRepository, ExerciseRenderer $exerciseRenderer)
    {
        $this->mainMenu = new CliMenu(
            'PHP School Workshop',
            array_map(function ($exerciseName) {
                return new MenuItem($exerciseName);
            }, $exerciseRepository->getAllNames()),
            $exerciseRenderer,
            [
                new SelectableItem('Options', [$this, 'showOptionsMenu'])
            ]
        );

        $this->optionsMenu = new CliMenu(
            'Advanced CLI Menu > Options',
            [
                new SelectableItem('Select language', function (CliMenu $menu) {
                    echo "Select language";
                }),
                new SelectableItem('Reset workshop progress', function (CliMenu $menu) {
                    //reset here
                }),
            ],
            function (CliMenu $menu) {

            },
            [
                new SelectableItem('Go Back', [$this, 'showMainMenu'])
            ],
            null,
            new MenuStyle('red')
        );
    }

    /**
     * Show the options menu
     */
    public function showOptionsMenu()
    {
        $this->optionsMenu->display();
    }

    /**
     * Show the mai menu
     */
    public function showMainMenu()
    {
        $this->mainMenu->display();
    }

    /**
     * Show the menu
     */
    public function display()
    {
        $this->mainMenu->display();
    }
}
