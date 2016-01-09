<?php

namespace PhpSchool\PhpWorkshop\Command;

use Colors\Color;
use PhpSchool\PhpWorkshop\Output\OutputInterface;

/**
 * Class HelpCommand
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class HelpCommand
{

    /**
     * @var string
     */
    private $appName;
    
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Color
     */
    private $color;

    /**
     * @param string $appName
     * @param OutputInterface $output
     * @param Color $color
     */
    public function __construct($appName, OutputInterface $output, Color $color)
    {
        $this->output   = $output;
        $this->color    = $color;
        $this->appName  = $appName;
    }
    
    /**
     * @return void
     */
    public function __invoke()
    {
        $this->output->writeLine($this->color->__invoke('Usage')->yellow()->bold());
        $this->output->writeLine("");
        $this->output->writeLine(sprintf("  %s", $this->color->__invoke($this->appName)->green()));
        $this->output->writeLine("    Show a menu to interactively select a workshop.");
        $this->output->writeLine(sprintf("  %s print", $this->color->__invoke($this->appName)->green()));
        $this->output->writeLine("    Print the instructions for the currently selected workshop.");
        $this->output->writeLine(sprintf("  %s verify program.php", $this->color->__invoke($this->appName)->green()));
        $this->output->writeLine("    Verify your program against the expected output.");
        $this->output->writeLine(sprintf("  %s help", $this->color->__invoke($this->appName)->green()));
        $this->output->writeLine("    Show this help.");
        $this->output->writeLine(sprintf("  %s credits", $this->color->__invoke($this->appName)->green()));
        $this->output->writeLine("    Show the people who made this happen.");
        $this->output->writeLine("");
        $this->output->writeLine(
            $this->color->__invoke('Having trouble with a PHPSchool exercise?')->yellow()->bold()
        );
        $this->output->writeLine("");
        $this->output->writeLine("  A team of expert helper elves is eagerly waiting to assist you in");
        $this->output->writeLine("  mastering the basics of PHP, simply go to:");
        $this->output->writeLine("    https://github.com/php-school/discussions");
        $this->output->writeLine("  and add a New Issue and let us know what you're having trouble");
        $this->output->writeLine("  with. There are no dumb questions!");
        $this->output->writeLine("");
        $this->output->writeLine("  If you're looking for general help with PHP, the #php");
        $this->output->writeLine("  channel on Freenode IRC is usually a great place to find someone");
        $this->output->writeLine("  willing to help. There is also the PHP StackOverflow Chat:");
        $this->output->writeLine("    https://chat.stackoverflow.com/rooms/11/php");
        $this->output->writeLine("");
        $this->output->writeLine(
            $this->color->__invoke('Found a bug with PHPSchool or just want to contribute?')->yellow()->bold()
        );
        $this->output->writeLine("  The official repository for PHPSchool is:");
        $this->output->writeLine("    https://github.com/php-school/php-workshop");
        $this->output->writeLine("  Feel free to file a bug report or (preferably) a pull request.");
        $this->output->writeLine("");
    }
}
