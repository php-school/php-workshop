<?php

namespace PhpSchool\PhpWorkshop\Command;

use Colors\Color;
use PhpSchool\PhpWorkshop\Output;

/**
 * Class CreditsCommand
 * @package PhpSchool\PhpWorkshop\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CreditsCommand
{
    /**
     * @var Output
     */
    private $output;
    
    /**
     * @var Color
     */
    private $color;

    /**
     * @param Output $output
     * @param Color $color
     */
    public function __construct(Output $output, Color $color)
    {
        $this->output = $output;
        $this->color = $color;
    }
    
    /**
     * @return int|void
     */
    public function __invoke()
    {
        if (!file_exists(__DIR__ . '/../../credits.txt')) {
            return 1;
        }
        
        $contributors = file(__DIR__ . '/../../credits.txt', FILE_IGNORE_NEW_LINES);
        $names = [];
        foreach ($contributors as $contributor) {
            $parts      = preg_split('/\s/', $contributor);
            $gitHubUser = array_pop($parts);
            
            $names[implode(" ", $parts)] = $gitHubUser;
        }
        
        $longest = max(array_map('strlen', array_flip($names)));

        $this->output->writeLine(
            $this->color->__invoke("PHP School is bought to you by the following hackers")->yellow()
        );
        $this->output->writeLine("");
        $line = sprintf('%s GitHub Username', str_pad('Name', $longest));
        $this->output->writeLine($line);
        $this->output->writeLine(str_repeat('-', strlen($line)));
        foreach ($names as $name => $gitHubUser) {
            $this->output->writeLine(sprintf("%s %s", str_pad($name, $longest), $gitHubUser));
        }
    }
}
