<?php

namespace PhpSchool\PhpWorkshop\MenuItem;

use PhpSchool\CliMenu\CliMenu;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshop\UserStateSerializer;

/**
 * Class ResetProgress
 * @package PhpSchool\PhpWorkshop\MenuItem
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ResetProgress
{
    /**
     * @var UserStateSerializer
     */
    private $userStateSerializer;

    /**
     * @param UserStateSerializer $userStateSerializer
     */
    public function __construct(UserStateSerializer $userStateSerializer)
    {
        $this->userStateSerializer = $userStateSerializer;
    }

    /**
     * @param CliMenu $menu
     */
    public function __invoke(CliMenu $menu)
    {
        $this->userStateSerializer->serialize(new UserState);

        $items = $menu
            ->getParent()
            ->getItems();

        foreach ($items as $item) {
            $item->hideItemExtra();
        }

        $confirm = $menu->confirm('Status Reset!');
        $confirm->getStyle()->setBg('magenta')->setFg('black');
        $confirm->display('OK');
    }
}
