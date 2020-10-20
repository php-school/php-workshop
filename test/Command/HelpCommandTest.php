<?php

namespace PhpSchool\PhpWorkshopTest\Command;

use Colors\Color;
use PhpSchool\Terminal\Terminal;
use PhpSchool\PhpWorkshop\Command\HelpCommand;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use PHPUnit\Framework\TestCase;

/**
 * Class HelpCommandTest
 * @package PhpSchool\PhpWorkshop\Command
 * @author Michael Woodward <aydin@hotmail.co.uk>
 */
class HelpCommandTest extends TestCase
{
    public function testInvoke() : void
    {
        $this->expectOutputString(file_get_contents(__DIR__ . '/../res/app-help-expected.txt'));

        $color = new Color;
        $color->setForceStyle(true);

        $command = new HelpCommand(
            'learnyouphp',
            new StdOutput($color, $this->createMock(Terminal::class)),
            $color
        );

        $command->__invoke();
    }
}
