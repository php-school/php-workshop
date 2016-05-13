<?php

namespace PhpSchool\PhpWorkshop;

use Assert\Assertion;
use DI\ContainerBuilder;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Check\CheckRepository;
use PhpSchool\PhpWorkshop\Exception\MissingArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultRendererInterface;

/**
 * Class Application
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
final class Application
{
    /**
     * @var string
     */
    private $workshopTitle;

    /**
     * @var array
     */
    private $checks = [];

    /**
     * @var ExerciseInterface[]
     */
    private $exercises = [];

    /**
     * @var string
     */
    private $diConfigFile;

    /**
     * @var string
     */
    private $logo = null;

    /**
     * @var string
     */
    private $fgColour = 'green';

    /**
     * @var string
     */
    private $bgColour = 'black';

    /**
     * @param string $workshopTitle
     * @param $diConfigFile
     */
    public function __construct($workshopTitle, $diConfigFile)
    {
        Assertion::string($workshopTitle);
        Assertion::file($diConfigFile);
        
        $this->workshopTitle = $workshopTitle;
        $this->diConfigFile = $diConfigFile;
    }

    /**
     * @param string $check
     */
    public function addCheck($check)
    {
        $this->checks[] = $check;
    }

    /**
     * @param string $exercise
     */
    public function addExercise($exercise)
    {
        $this->exercises[] = $exercise;
    }

    /**
     * @param string $logo
     */
    public function setLogo($logo)
    {
        Assertion::string($logo);
        $this->logo = $logo;
    }

    /**
     * @param string $colour
     */
    public function setFgColour($colour)
    {
        Assertion::string($colour);
        $this->fgColour = $colour;
    }

    /**
     * @param string $colour
     */
    public function setBgColour($colour)
    {
        Assertion::string($colour);
        $this->bgColour = $colour;
    }

    /**
     * Run the app
     */
    public function run()
    {
        $containerBuilder = new ContainerBuilder;
        $containerBuilder->addDefinitions(__DIR__ . '/../app/config.php');
        $containerBuilder->addDefinitions($this->diConfigFile);
        
        $containerBuilder->addDefinitions(array_merge(
            [
                'workshopTitle' => $this->workshopTitle,
                'exercises'     => $this->exercises,
                'workshopLogo'  => $this->logo,
                'bgColour'      => $this->bgColour,
                'fgColour'      => $this->fgColour,
            ]
        ));
        
        $containerBuilder->useAutowiring(false);
        $containerBuilder->useAnnotations(false);

        $container = $containerBuilder->build();
        
        foreach ($this->exercises as $exercise) {
            if (false === $container->has($exercise)) {
                throw new \RuntimeException(
                    sprintf('No DI config found for exercise: "%s". Register a factory.', $exercise)
                );
            }
        }

        $checkRepository = $container->get(CheckRepository::class);
        foreach ($this->checks as $check) {
            if (false === $container->has($check)) {
                throw new \RuntimeException(
                    sprintf('No DI config found for check: "%s". Register a factory.', $check)
                );
            }

            $checkRepository->registerCheck($container->get($check));
        }

        try {
            $exitCode = $container->get(CommandRouter::class)->route();
        } catch (MissingArgumentException $e) {
            $container
                ->get(OutputInterface::class)
                ->printError(
                    sprintf(
                        'Argument%s: "%s" %s missing!',
                        count($e->getMissingArguments()) > 1 ? 's' : '',
                        implode('", "', $e->getMissingArguments()),
                        count($e->getMissingArguments()) > 1 ? 'are' : 'is'
                    )
                );
            return 1;
        }
        return $exitCode;
    }
}
