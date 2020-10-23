<?php

namespace PhpSchool\PhpWorkshopTest\Command;

use Colors\Color;
use PhpSchool\Terminal\Terminal;
use PhpSchool\PhpWorkshop\Command\CreditsCommand;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use PHPUnit\Framework\TestCase;

class CreditsCommandTest extends TestCase
{
    public function testInvoke(): void
    {
        $this->expectOutputString(file_get_contents(__DIR__ . '/../res/app-credits-expected.txt'));

        $color = new Color();
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
            new StdOutput($color, $this->createMock(Terminal::class)),
            $color
        );

        $command->__invoke();
    }

    public function testWithOnlyCoreContributors(): void
    {
        $this->expectOutputString(file_get_contents(__DIR__ . '/../res/app-credits-core-expected.txt'));

        $color = new Color();
        $color->setForceStyle(true);

        $command = new CreditsCommand(
            [
                '@AydinHassan' => 'Aydin Hassan',
                '@mikeymike'   => 'Michael Woodward',
                '@shakeyShane' => 'Shane Osbourne',
                '@chris3ailey' => 'Chris Bailey'
            ],
            [],
            new StdOutput($color, $this->createMock(Terminal::class)),
            $color
        );

        $command->__invoke();
    }

    public function testWithNoContributors(): void
    {
        $this->expectOutputString('');

        $color = new Color();
        $color->setForceStyle(true);

        $command = new CreditsCommand(
            [],
            [],
            new StdOutput($color, $this->createMock(Terminal::class)),
            $color
        );

        $command->__invoke();
    }
}
