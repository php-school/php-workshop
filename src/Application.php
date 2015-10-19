<?php

namespace PhpWorkshop\PhpWorkshop;

use Assert\Assertion;
use DI\ContainerBuilder;
use PhpWorkshop\PhpWorkshop\Check\CheckInterface;
use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\ResultRenderer\ResultRendererInterface;

/**
 * Class Application
 * @package PhpWorkshop\PhpWorkshop
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
     * @var array
     */
    private $renderers = [];

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
     * @param string $exerciseInterface
     */
    public function addCheck(CheckInterface $check, $exerciseInterface)
    {
        //TODO Use reflection to check that $exerciseInterface exists and is an interface
        $this->checks[] = [$check, $exerciseInterface];
    }

    /**
     * @param ExerciseInterface $exercise
     */
    public function addExercise($exercise)
    {
        $this->exercises[] = $exercise;
    }

    /**
     * @param ResultRendererInterface $renderer
     * @param string $resultClass
     */
    public function addRenderer(ResultRendererInterface $renderer, $resultClass)
    {
        //TODO Use reflection to check that $resultClass exists and implements ResultInterface
        $this->renderers[] = [$renderer, $resultClass];
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
        
        $renderers = $container->get('renderers');
        $container->set('renderers', array_merge($renderers, $this->renderers));
        
        $checks = $container->get('checks');
        $container->set('checks', array_merge($checks, $this->checks));
        
        $router = $container->get(CommandRouter::class);
        return $router->route();
    }
}
