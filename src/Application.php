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
        Assertion::file($diConfigFile);
        
        $this->workshopTitle = $workshopTitle;
        $this->diConfigFile = $diConfigFile;
    }

    /**
     * @param CheckInterface $check
     */
    public function addCheck(CheckInterface $check)
    {
        $this->checks[] = $check;
    }

    /**
     * @param ExerciseInterface $exercise
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
        array_walk($this->checks, function (CheckInterface $check) use ($checkRepository) {
            $checkRepository->registerCheck($check);
        });

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
