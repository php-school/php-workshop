<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\MenuItem;

use PhpSchool\CliMenu\CliMenu;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshop\UserStateSerializer;

/**
 * Menu action to reset the workshop progress
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
    public function __invoke(CliMenu $menu): void
    {
        $this->userStateSerializer->serialize(new UserState());

        $parent = $menu->getParent();

        if (!$parent) {
            return;
        }

        $items = $parent->getItems();

        foreach ($items as $item) {
            $item->hideItemExtra();
        }

        $confirm = $menu->confirm('Status Reset!');
        $confirm->getStyle()->setBg('magenta')->setFg('black');
        $confirm->display('OK');
    }
}
