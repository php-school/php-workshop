<?php

namespace PhpSchool\PhpWorkshopTest\Command;

use Colors\Color;
use PhpSchool\PhpWorkshop\Command\CreditsCommand;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use PHPUnit_Framework_TestCase;

/**
 * Class CreditsCommandTest
 * @package PhpSchool\PhpWorkshop\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 * @author Michael Woodward <mikeymike.mw@gmail.com>
 */
class CreditsCommandTest extends PHPUnit_Framework_TestCase
{

    public function testInvoke()
    {
        $this->expectOutputString(file_get_contents(__DIR__ . '/../res/app-credits-expected.txt'));

        $color = new Color;
        $color->setForceStyle(true);

        $command = new CreditsCommand(
            [
                '@AydinHassan' => 'Aydin Hassan',
                '@mikeymike'   => 'Michael Woodward',
                '@shakeyShane' => 'Shane Osbourne',
                '@chris3ailey' => 'Chris Bailey'
            ],
            [
                '@AydinHassan' => 'Aydin Hassan',
                '@mikeymike'   => 'Michael Woodward',
            ],
            new StdOutput($color),
            $color
        );

        $command->__invoke();
    }

    public function testWithOnlyCoreContributors()
    {
        $this->expectOutputString(file_get_contents(__DIR__ . '/../res/app-credits-core-expected.txt'));

        $color = new Color;
        $color->setForceStyle(true);

        $command = new CreditsCommand(
            [
                '@AydinHassan' => 'Aydin Hassan',
                '@mikeymike'   => 'Michael Woodward',
                '@shakeyShane' => 'Shane Osbourne',
                '@chris3ailey' => 'Chris Bailey'
            ],
            [],
            new StdOutput($color),
            $color
        );

        $command->__invoke();
    }

    public function testWithNoContributors()
    {
        $this->expectOutputString('');
        
        $color = new Color;
        $color->setForceStyle(true);
        
        $command = new CreditsCommand(
            [],
            [],
            new StdOutput($color),
            $color
        );
        
        $command->__invoke();
    }
}
