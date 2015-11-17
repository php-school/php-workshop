<?php

namespace PhpSchool\PhpWorkshop\Command;

use Colors\Color;
use PhpSchool\PhpWorkshop\Output;

/**
 * Class CreditsCommand
 * @package PhpSchool\PhpWorkshop\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 * @author Michael Woodward <mikeymike.mw@gmail.com>
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
     * @var array
     */
    private $coreContributors;

    /**
     * @var array
     */
    private $appContributors;

    /**
     * @param array $coreContributors
     * @param array $appContributors
     * @param Output $output
     * @param Color $color
     */
    public function __construct(array $coreContributors, array $appContributors, Output $output, Color $color)
    {
        $this->coreContributors = $coreContributors;
        $this->appContributors  = $appContributors;
        $this->output           = $output;
        $this->color            = $color;
    }

    /**
     * Output contributors in columns
     *
     * @param array $contributors
     */
    private function writeContributors(array $contributors)
    {
        $nameColumnSize = max(array_map('strlen', array_values($contributors)));
        $columns        = sprintf('%s  GitHub Username', str_pad('Name', $nameColumnSize));

        $this->output->writeLine($columns);
        $this->output->writeLine(str_repeat('-', strlen($columns)));

        foreach ($contributors as $gitHubUser => $name) {
            $this->output->writeLine(sprintf("%s  %s", str_pad($name, $nameColumnSize), $gitHubUser));
        }
    }
    
    /**
     * @return int|void
     */
    public function __invoke()
    {
        $this->output->writeLine(
            $this->color->__invoke("PHP School is bought to you by...")->yellow()->__toString()
        );
        $this->output->writeLine("");
        $this->writeContributors($this->coreContributors);

        $this->output->writeLine("");
        $this->output->writeLine("");

        $this->output->writeLine(
            $this->color->__invoke("This workshop is brought to you by...")->yellow()->__toString()
        );
        $this->output->writeLine("");
        $this->writeContributors($this->appContributors);

    }
}
