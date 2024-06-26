<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop;

use DI\Container;
use DI\ContainerBuilder;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Check\CheckRepository;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exception\MissingArgumentException;
use PhpSchool\PhpWorkshop\Factory\ResultRendererFactory;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

use function class_exists;
use function is_file;
use function sprintf;

/**
 * This is the main application class, this takes care of bootstrapping, routing and
 * output.
 */
final class Application
{
    /**
     * @var string
     */
    private $workshopTitle;

    /**
     * @var array<class-string>
     */
    private $checks = [];

    /**
     * @var array<class-string>
     */
    private $exercises = [];

    /**
     * @var array<array{resultClass: class-string, resultRendererClass: class-string}>
     */
    private $results = [];

    /**
     * @var string
     */
    private $diConfigFile;

    /**
     * @var string|null
     */
    private $logo;

    /**
     * @var string
     */
    private $fgColour = 'green';

    /**
     * @var string
     */
    private $bgColour = 'black';

    /**
     * @var string
     */
    private $frameworkConfigLocation = __DIR__ . '/../app/config.php';

    /**
     * @var ?ContainerInterface
     */
    private $container;

    /**
     * It should be instantiated with the title of
     * the workshop and the path to the DI configuration file.
     *
     * @param string $workshopTitle The workshop title - this is used throughout the application
     * @param string $diConfigFile The absolute path to the DI configuration file
     */
    public function __construct(string $workshopTitle, string $diConfigFile)
    {
        if (!is_file($diConfigFile)) {
            throw new InvalidArgumentException(sprintf('File "%s" was expected to exist.', $diConfigFile));
        }

        $this->workshopTitle = $workshopTitle;
        $this->diConfigFile = $diConfigFile;
    }

    /**
     * Register a custom check with the application. Exercises will only be able to use the check
     * if it has been registered here.
     *
     * @param class-string $check The FQCN of the check
     */
    public function addCheck(string $check): void
    {
        $this->checks[] = $check;
    }

    /**
     * Register an exercise with the application. Only exercises registered here will
     * be displayed in the exercise menu.
     *
     * @param class-string $exercise The FQCN of the check
     */
    public function addExercise(string $exercise): void
    {
        $this->exercises[] = $exercise;
    }

    /**
     * @param class-string $resultClass
     * @param class-string $resultRendererClass
     */
    public function addResult(string $resultClass, string $resultRendererClass): void
    {
        if (!class_exists($resultClass)) {
            throw new InvalidArgumentException(sprintf('Class "%s" does not exist', $resultClass));
        }

        if (!class_exists($resultRendererClass)) {
            throw new InvalidArgumentException(sprintf('Class "%s" does not exist', $resultRendererClass));
        }

        $this->results[] = [
            'resultClass' => $resultClass,
            'resultRendererClass' => $resultRendererClass,
        ];
    }

    /**
     * Add an ASCII art logo to the application. This will be displayed at the top of them menu. It will be
     * automatically padded to sit in the middle.
     *
     * @param string $logo The logo
     */
    public function setLogo(string $logo): void
    {
        $this->logo = $logo;
    }

    /**
     * Modify the foreground color of the workshop menu
     * Can be any of: black, red, green, yellow, blue, magenta, cyan, white
     *
     * @param string $colour The colour
     */
    public function setFgColour(string $colour): void
    {
        $this->fgColour = $colour;
    }

    /**
     * Modify the background color of the workshop menu
     * Can be any of: black, red, green, yellow, blue, magenta, cyan, white
     *
     * @param string $colour The colour
     */
    public function setBgColour(string $colour): void
    {
        $this->bgColour = $colour;
    }

    public function configure(bool $debugMode = false): ContainerInterface
    {
        if ($this->container instanceof ContainerInterface) {
            return $this->container;
        }

        $container = $this->getContainer($debugMode);

        foreach ($this->exercises as $exercise) {
            if (false === $container->has($exercise)) {
                throw new RuntimeException(
                    sprintf('No DI config found for exercise: "%s". Register a factory.', $exercise),
                );
            }
        }

        /** @var CheckRepository $checkRepository */
        $checkRepository = $container->get(CheckRepository::class);
        foreach ($this->checks as $check) {
            if (false === $container->has($check)) {
                throw new RuntimeException(
                    sprintf('No DI config found for check: "%s". Register a factory.', $check),
                );
            }

            $checkInstance = $container->get($check);

            if (!$checkInstance instanceof CheckInterface) {
                throw new RuntimeException(
                    sprintf(
                        'Check: "%s" does not implement the required interface: "%s".',
                        $check,
                        CheckInterface::class,
                    ),
                );
            }

            $checkRepository->registerCheck($checkInstance);
        }

        if (!empty($this->results)) {
            /** @var ResultRendererFactory $resultFactory */
            $resultFactory = $container->get(ResultRendererFactory::class);

            foreach ($this->results as $result) {
                $resultFactory->registerRenderer($result['resultClass'], $result['resultRendererClass']);
            }
        }

        //turn all notices & warnings in to exceptions
        set_error_handler(function (int $severity, string $message, string $file, int $line) {
            //ignore silenced errors
            if (!(error_reporting() & $severity)) {
                return false;
            }

            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        $this->container = $container;
        return $container;
    }

    /**
     * Executes the framework, invoking the specified command.
     * The return value is the exit code. 0 for success, anything else is a failure.
     *
     * @return int The exit code
     */
    public function run(): int
    {
        $args = $_SERVER['argv'] ?? [];

        $debug = any($args, function (string $arg) {
            return $arg === '--debug';
        });

        $args = array_values(array_filter($args, function (string $arg) {
            return $arg !== '--debug';
        }));

        $container = $this->configure($debug);

        try {
            /** @var CommandRouter $router */
            $router = $container->get(CommandRouter::class);
            $exitCode = $router->route($args);
        } catch (MissingArgumentException $e) {
            /** @var OutputInterface $output */
            $output = $container->get(OutputInterface::class);
            $output->printError(
                sprintf(
                    'Argument%s: "%s" %s missing!',
                    count($e->getMissingArguments()) > 1 ? 's' : '',
                    implode('", "', $e->getMissingArguments()),
                    count($e->getMissingArguments()) > 1 ? 'are' : 'is',
                ),
            );
            return 1;
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            $basePath = canonicalise_path(is_string($path  = $container->get('basePath')) ? $path : '');

            if (strpos($message, $basePath) !== null) {
                $message = str_replace($basePath, '', $message);
            }

            $this->tearDown($container);

            /** @var OutputInterface $output */
            $output = $container->get(OutputInterface::class);
            $output->printException($e);

            return 1;
        }

        $this->tearDown($container);

        return $exitCode;
    }

    /**
     * @param bool $debugMode
     * @return Container
     */
    private function getContainer(bool $debugMode): Container
    {
        $containerBuilder = new ContainerBuilder();
        $diConfig = require $this->diConfigFile;
        $fwConfig = require $this->frameworkConfigLocation;

        $fwConfig['eventListeners'] = array_merge_recursive(
            $fwConfig['eventListeners'],
            $diConfig['eventListeners'] ?? [],
        );

        unset($diConfig['eventListeners']);

        $containerBuilder->addDefinitions($diConfig, $fwConfig);

        $containerBuilder->addDefinitions(
            [
                'workshopTitle' => $this->workshopTitle,
                'debugMode'     => $debugMode,
                'exercises'     => $this->exercises,
                'workshopLogo'  => $this->logo,
                'bgColour'      => $this->bgColour,
                'fgColour'      => $this->fgColour,
            ],
        );

        $containerBuilder->useAutowiring(false);
        $containerBuilder->useAnnotations(false);

        return $containerBuilder->build();
    }

    private function tearDown(ContainerInterface $container): void
    {
        try {
            /** @var EventDispatcher $eventDispatcher */
            $eventDispatcher =  $container->get(EventDispatcher::class);
            $eventDispatcher->dispatch(new Event('application.tear-down'));
        } catch (\Throwable $t) {
            /** @var LoggerInterface $logger */
            $logger = $container->get(LoggerInterface::class);
            $logger->error($t->getMessage(), ['exception' => $t]);
        }
    }
}
