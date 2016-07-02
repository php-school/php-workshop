<?php

namespace PhpSchool\PhpWorkshop;

use Assert\Assertion;
use DI\ContainerBuilder;
use PhpSchool\PhpWorkshop\Check\CheckRepository;
use PhpSchool\PhpWorkshop\Exception\MissingArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Factory\ResultRendererFactory;
use PhpSchool\PhpWorkshop\Output\OutputInterface;

/**
 * This is the main application class, this takes care of bootstrapping, routing and
 * output.
 *
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
     * @var array
     */
    private $results = [];

    /**
     * @var string
     */
    private $diConfigFile;

    /**
     * @var string|null
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
     * It should be instantiated with the title of
     * the workshop and the path to the DI configuration file.
     *
     * @param string $workshopTitle The workshop title - this is used throughout the application
     * @param string $diConfigFile The absolute path to the DI configuration file
     */
    public function __construct($workshopTitle, $diConfigFile)
    {
        Assertion::string($workshopTitle);
        Assertion::file($diConfigFile);
        
        $this->workshopTitle = $workshopTitle;
        $this->diConfigFile = $diConfigFile;
    }

    /**
     * Register a custom check with the application. Exercises will only be use the check
     * if it has been registered here.
     *
     * @param string $check The FQCN of the check
     */
    public function addCheck($check)
    {
        $this->checks[] = $check;
    }

    /**
     * Register an exercise with the application. Only exercises registered here will
     * be displayed in the exercise menu.
     *
     * @param string $exercise The FQCN of the check
     */
    public function addExercise($exercise)
    {
        $this->exercises[] = $exercise;
    }

    /**
     * @param string $resultClass
     * @param string $resultRendererClass
     */
    public function addResult($resultClass, $resultRendererClass)
    {
        Assertion::classExists($resultClass);
        Assertion::classExists($resultRendererClass);

        $this->results[] = [
            'resultClass' => $resultClass,
            'resultRendererClass' => $resultRendererClass
        ];
    }

    /**
     * Add an ASCII art logo to the application. This will be displayed a the top of them menu. It will be
     * automatically padded to sit in the middle.
     *
     * @param string $logo The logo
     */
    public function setLogo($logo)
    {
        Assertion::string($logo);
        $this->logo = $logo;
    }

    /**
     * Modify the foreground color of the workshop menu
     * Can be any of: black, red, green, yellow, blue, magenta, cyan, white
     *
     * @param string $colour The colour
     */
    public function setFgColour($colour)
    {
        Assertion::string($colour);
        $this->fgColour = $colour;
    }

    /**
     * Modify the background color of the workshop menu
     * Can be any of: black, red, green, yellow, blue, magenta, cyan, white
     *
     * @param string $colour The colour
     */
    public function setBgColour($colour)
    {
        Assertion::string($colour);
        $this->bgColour = $colour;
    }

    /**
     * Executes the framework, invoking the specified command.
     * The return value is the exit code. 0 for success, anything else is a failure.
     *
     * @return int The exit code
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

        if (!empty($this->results)) {
            $resultFactory = $container->get(ResultRendererFactory::class);

            foreach ($this->results as $result) {
                $resultFactory->registerRenderer($result['resultClass'], $result['resultRendererClass']);
            }
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
        } catch (\RuntimeException $e) {
            $container
                ->get(OutputInterface::class)
                ->printError(
                    sprintf(
                        '%s',
                        $e->getMessage()
                    )
                );
            return 1;
        }
        return $exitCode;
    }
}
