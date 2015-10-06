<?php

namespace PhpWorkshop\PhpWorkshopTest\Command;

use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Command\HelpCommand;

/**
 * Class HelpCommandTest
 * @package PhpWorkshop\PhpWorkshop\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class HelpCommandTest extends PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $this->expectOutputString("HELPPPP\n");
        $command = new HelpCommand;
        $command->__invoke();
    }
}
