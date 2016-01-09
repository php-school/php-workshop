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
     * @var OutputInterface
     */
    private $output;

    /**
     * @param UserStateSerializer $userStateSerializer
     * @param OutputInterface $output
     */
    public function __construct(UserStateSerializer $userStateSerializer, OutputInterface $output)
    {
        $this->userStateSerializer = $userStateSerializer;
        $this->output = $output;
    }

    /**
     * @param CliMenu $menu
     */
    public function __invoke(CliMenu $menu)
    {
        $this->userStateSerializer->serialize(new UserState);
        $this->output->writeLine("Status Reset!");
    }
}
