<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Command;

use Colors\Color;
use PhpSchool\PhpWorkshop\Output\OutputInterface;

/**
 * A command to display the framework and workshop credits.
 */
class CreditsCommand
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Color
     */
    private $color;

    /**
     * @var array<string>
     */
    private $coreContributors;

    /**
     * @var array<string>
     */
    private $appContributors;

    /**
     * @param array<string> $coreContributors
     * @param array<string> $appContributors
     * @param OutputInterface $output
     * @param Color $color
     */
    public function __construct(array $coreContributors, array $appContributors, OutputInterface $output, Color $color)
    {
        $this->coreContributors = $coreContributors;
        $this->appContributors = $appContributors;
        $this->output = $output;
        $this->color = $color;
    }

    /**
     * Output contributors in columns
     *
     * @param array<string> $contributors
     */
    private function writeContributors(array $contributors): void
    {
        $nameColumnSize = max(array_map('strlen', array_values($contributors)));
        $columns        = sprintf('%s  GitHub Username', str_pad('Name', (int) $nameColumnSize));

        $this->output->writeLine($columns);
        $this->output->writeLine(str_repeat('-', strlen($columns)));

        foreach ($contributors as $gitHubUser => $name) {
            $this->output->writeLine(sprintf("%s  %s", str_pad($name, (int) $nameColumnSize), $gitHubUser));
        }
    }

    /**
     *
     * @return void
     */
    public function __invoke(): void
    {
        if (empty($this->coreContributors)) {
            return;
        }

        $this->output->writeLine(
            $this->color->__invoke("PHP School is bought to you by...")->yellow()->__toString(),
        );
        $this->output->emptyLine();
        $this->writeContributors($this->coreContributors);

        if (empty($this->appContributors)) {
            return;
        }

        $this->output->emptyLine();
        $this->output->emptyLine();

        $this->output->writeLine(
            $this->color->__invoke("This workshop is brought to you by...")->yellow()->__toString(),
        );
        $this->output->writeLine("");
        $this->writeContributors($this->appContributors);
    }
}
