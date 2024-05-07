<?php

declare(strict_types=1);

use Colors\Color;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Kadet\Highlighter\KeyLighter;
use League\CommonMark\DocParser;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Environment;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpSchool\CliMdRenderer\CliExtension;
use PhpSchool\CliMdRenderer\CliRenderer;
use PhpSchool\CliMenu\Terminal\TerminalFactory;
use PhpSchool\PhpWorkshop\Check\CheckRepository;
use PhpSchool\PhpWorkshop\Check\CodeExistsCheck;
use PhpSchool\PhpWorkshop\Check\CodeParseCheck;
use PhpSchool\PhpWorkshop\Check\ComposerCheck;
use PhpSchool\PhpWorkshop\Check\DatabaseCheck;
use PhpSchool\PhpWorkshop\Check\FileComparisonCheck;
use PhpSchool\PhpWorkshop\Check\FileExistsCheck;
use PhpSchool\PhpWorkshop\Check\FunctionRequirementsCheck;
use PhpSchool\PhpWorkshop\Check\PhpLintCheck;
use PhpSchool\PhpWorkshop\CodeInsertion as Insertion;
use PhpSchool\PhpWorkshop\CodePatcher;
use PhpSchool\PhpWorkshop\Command\CreditsCommand;
use PhpSchool\PhpWorkshop\Command\HelpCommand;
use PhpSchool\PhpWorkshop\Command\MenuCommand;
use PhpSchool\PhpWorkshop\Command\PrintCommand;
use PhpSchool\PhpWorkshop\Command\RunCommand;
use PhpSchool\PhpWorkshop\Command\VerifyCommand;
use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\CommandRouter;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\ExerciseRenderer;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContextFactory;
use PhpSchool\PhpWorkshop\ExerciseRunner\Factory\CgiRunnerFactory;
use PhpSchool\PhpWorkshop\ExerciseRunner\Factory\CliRunnerFactory;
use PhpSchool\PhpWorkshop\ExerciseRunner\Factory\CustomVerifyingRunnerFactory;
use PhpSchool\PhpWorkshop\ExerciseRunner\Factory\ServerRunnerFactory;
use PhpSchool\PhpWorkshop\ExerciseRunner\RunnerManager;
use PhpSchool\PhpWorkshop\Factory\EventDispatcherFactory;
use PhpSchool\PhpWorkshop\Factory\MenuFactory;
use PhpSchool\PhpWorkshop\Factory\ResultRendererFactory;
use PhpSchool\PhpWorkshop\Listener\CheckExerciseAssignedListener;
use PhpSchool\PhpWorkshop\Listener\CodePatchListener;
use PhpSchool\PhpWorkshop\Listener\ConfigureCommandListener;
use PhpSchool\PhpWorkshop\Listener\InitialCodeListener;
use PhpSchool\PhpWorkshop\Listener\OutputRunInfoListener;
use PhpSchool\PhpWorkshop\Listener\PrepareSolutionListener;
use PhpSchool\PhpWorkshop\Listener\RealPathListener;
use PhpSchool\PhpWorkshop\Listener\SelfCheckListener;
use PhpSchool\PhpWorkshop\Listener\TearDownListener;
use PhpSchool\PhpWorkshop\Logger\ConsoleLogger;
use PhpSchool\PhpWorkshop\Logger\Logger;
use PhpSchool\PhpWorkshop\Markdown\CurrentContext;
use PhpSchool\PhpWorkshop\Markdown\Parser\ContextSpecificBlockParser;
use PhpSchool\PhpWorkshop\Markdown\ProblemFileExtension;
use PhpSchool\PhpWorkshop\Markdown\Renderer\ContextSpecificRenderer;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\Cli\AppName;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\Cli\Run;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\Cli\Verify;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\Context;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\Documentation;
use PhpSchool\PhpWorkshop\MarkdownRenderer;
use PhpSchool\PhpWorkshop\MenuItem\ResetProgress;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use PhpSchool\PhpWorkshop\Patch;
use PhpSchool\PhpWorkshop\Process\HostProcessFactory;
use PhpSchool\PhpWorkshop\Process\ProcessFactory;
use PhpSchool\PhpWorkshop\Result\Cgi\CgiResult;
use PhpSchool\PhpWorkshop\Result\Cgi\GenericFailure as CgiGenericFailure;
use PhpSchool\PhpWorkshop\Result\Cgi\RequestFailure as CgiRequestFailure;
use PhpSchool\PhpWorkshop\Result\Cli\CliResult;
use PhpSchool\PhpWorkshop\Result\Cli\GenericFailure as CliGenericFailure;
use PhpSchool\PhpWorkshop\Result\Cli\RequestFailure as CliRequestFailure;
use PhpSchool\PhpWorkshop\Result\ComparisonFailure;
use PhpSchool\PhpWorkshop\Result\ComposerFailure;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\FileComparisonFailure;
use PhpSchool\PhpWorkshop\Result\FunctionRequirementsFailure;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\ResultRenderer\Cgi\RequestFailureRenderer as CgiRequestFailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\CgiResultRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\Cli\RequestFailureRenderer as CliRequestFailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\CliResultRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\ComparisonFailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\ComposerFailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\FailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\FileComparisonFailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\FunctionRequirementsFailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultsRenderer;
use PhpSchool\PhpWorkshop\UserState\LocalJsonSerializer;
use PhpSchool\PhpWorkshop\UserState\Serializer;
use PhpSchool\PhpWorkshop\UserState\UserState;
use PhpSchool\PhpWorkshop\Utils\RequestRenderer;
use PhpSchool\PhpWorkshop\WorkshopType;
use PhpSchool\Terminal\Terminal;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use function DI\create;
use function DI\factory;
use function PhpSchool\PhpWorkshop\canonicalise_path;
use function PhpSchool\PhpWorkshop\Event\containerListener;

return [
    'appName' => basename($_SERVER['argv'][0] ?? 'phpschool'),
    'phpschoolGlobalDir' => sprintf('%s/.php-school', getenv('HOME')),
    'currentWorkingDirectory' => function (ContainerInterface $c) {
        return getcwd();
    },
    'basePath' => DI\decorate(function (string $previous) {
        return canonicalise_path($previous);
    }),
    WorkshopType::class => WorkshopType::STANDARD(),
    Psr\Log\LoggerInterface::class => function (ContainerInterface $c) {
        $appName = $c->get('appName');
        $globalDir = $c->get('phpschoolGlobalDir');

        if ($c->get('debugMode')) {
            return new ConsoleLogger($c->get(OutputInterface::class), $c->get(Color::class));
        }

        return new Logger("$globalDir/logs/$appName.log");
    },
    ExerciseDispatcher::class => function (ContainerInterface $c) {
        return new ExerciseDispatcher(
            $c->get(RunnerManager::class),
            $c->get(ResultAggregator::class),
            $c->get(EventDispatcher::class),
            $c->get(CheckRepository::class),
            new ExecutionContextFactory()
        );
    },
    ResultAggregator::class => create(ResultAggregator::class),
    CheckRepository::class => function (ContainerInterface $c) {
        return new CheckRepository([
            $c->get(FileExistsCheck::class),
            $c->get(CodeExistsCheck::class),
            $c->get(PhpLintCheck::class),
            $c->get(CodeParseCheck::class),
            $c->get(ComposerCheck::class),
            $c->get(FunctionRequirementsCheck::class),
            $c->get(DatabaseCheck::class),
            $c->get(FileComparisonCheck::class)
        ]);
    },
    CommandRouter::class => function (ContainerInterface $c) {
        return new CommandRouter(
            [
                new CommandDefinition('menu', [], MenuCommand::class),
                new CommandDefinition('help', [], HelpCommand::class),
                new CommandDefinition('print', [], PrintCommand::class),
                new CommandDefinition('verify', [], VerifyCommand::class),
                new CommandDefinition('run', [], RunCommand::class),
                new CommandDefinition('credits', [], CreditsCommand::class)
            ],
            'menu',
            $c->get(EventDispatcher::class),
            $c
        );
    },

    Color::class => function () {
        $colors = new Color;
        $colors->setForceStyle(true);
        return $colors;
    },
    OutputInterface::class => function (ContainerInterface $c) {
        return new StdOutput($c->get(Color::class), $c->get(Terminal::class), $c->get('basePath'));
    },

    ExerciseRepository::class => function (ContainerInterface $c) {
        return new ExerciseRepository(
            array_map(function ($exerciseClass) use ($c) {
                return $c->get($exerciseClass);
            }, $c->get('exercises'))
        );
    },

    EventDispatcher::class => factory(EventDispatcherFactory::class),
    EventDispatcherFactory::class => create(),

    //Exercise Runners
    RunnerManager::class => function (ContainerInterface $c) {
        $manager = new RunnerManager();
        $manager->addFactory(new CliRunnerFactory($c->get(EventDispatcher::class), $c->get(ProcessFactory::class)));
        $manager->addFactory(new CgiRunnerFactory($c->get(EventDispatcher::class), $c->get(ProcessFactory::class)));
        $manager->addFactory(new CustomVerifyingRunnerFactory());
        return $manager;
    },

    ProcessFactory::class => function (ContainerInterface $c) {
        return new HostProcessFactory();
    },

    //commands
    MenuCommand::class => function (ContainerInterface $c) {
        return new MenuCommand($c->get('menu'));
    },

    PrintCommand::class => function (ContainerInterface $c) {
        return new PrintCommand(
            $c->get('appName'),
            $c->get(ExerciseRepository::class),
            $c->get(UserState::class),
            $c->get(MarkdownRenderer::class),
            $c->get(OutputInterface::class)
        );
    },

    VerifyCommand::class => function (ContainerInterface $c) {
        return new VerifyCommand(
            $c->get(ExerciseRepository::class),
            $c->get(ExerciseDispatcher::class),
            $c->get(UserState::class),
            $c->get(Serializer::class),
            $c->get(OutputInterface::class),
            $c->get(ResultsRenderer::class)
        );
    },

    RunCommand::class => function (ContainerInterface $c) {
        return new RunCommand(
            $c->get(ExerciseRepository::class),
            $c->get(ExerciseDispatcher::class),
            $c->get(UserState::class),
            $c->get(OutputInterface::class)
        );
    },

    CreditsCommand::class => function (ContainerInterface $c) {
        return new CreditsCommand(
            $c->get('coreContributors'),
            $c->get('appContributors'),
            $c->get(OutputInterface::class),
            $c->get(Color::class)
        );
    },

    HelpCommand::class => function (ContainerInterface $c) {
        return new HelpCommand(
            $c->get('appName'),
            $c->get(OutputInterface::class),
            $c->get(Color::class)
        );
    },

    //Listeners
    InitialCodeListener::class => function (ContainerInterface $c) {
        return new InitialCodeListener($c->get('currentWorkingDirectory'), $c->get(LoggerInterface::class));
    },
    PrepareSolutionListener::class => create(),
    CodePatchListener::class => function (ContainerInterface $c) {
        return new CodePatchListener(
            $c->get(CodePatcher::class),
            $c->get(LoggerInterface::class),
            $c->get('debugMode')
        );
    },
    SelfCheckListener::class => function (ContainerInterface $c) {
        return new SelfCheckListener($c->get(ResultAggregator::class));
    },
    CheckExerciseAssignedListener::class => function (ContainerInterface $c) {
        return new CheckExerciseAssignedListener($c->get(UserState::class));
    },
    ConfigureCommandListener::class => function (ContainerInterface $c) {
        return new ConfigureCommandListener(
            $c->get(UserState::class),
            $c->get(ExerciseRepository::class),
            $c->get(RunnerManager::class)
        );
    },
    RealPathListener::class => create(),
    TearDownListener::class => function (ContainerInterface $c) {
        return new TearDownListener($c->get(Filesystem::class));
    },

    OutputRunInfoListener::class => function (ContainerInterface  $c) {
        return new OutputRunInfoListener($c->get(OutputInterface::class), $c->get(RequestRenderer::class));
    },

    //checks
    FileExistsCheck::class              => create(),
    PhpLintCheck::class                 => create(),
    CodeExistsCheck::class              => function (ContainerInterface $c) {
        return new CodeExistsCheck($c->get(Parser::class));
    },
    CodeParseCheck::class               => function (ContainerInterface $c) {
        return new CodeParseCheck($c->get(Parser::class));
    },
    FunctionRequirementsCheck::class    => function (ContainerInterface $c) {
        return new FunctionRequirementsCheck($c->get(Parser::class));
    },
    DatabaseCheck::class                => create(),
    ComposerCheck::class                => create(),
    FileComparisonCheck::class => create(),

    //Utils
    Filesystem::class   => create(),
    Parser::class       => function () {
        $parserFactory = new ParserFactory;
        return $parserFactory->create(ParserFactory::PREFER_PHP7);
    },
    CodePatcher::class  => function (ContainerInterface $c) {
        $patch = (new Patch())
            ->withInsertion(new Insertion(Insertion::TYPE_BEFORE, 'ini_set("display_errors", "1");'))
            ->withInsertion(new Insertion(Insertion::TYPE_BEFORE, 'error_reporting(E_ALL);'))
            ->withInsertion(new Insertion(Insertion ::TYPE_BEFORE, 'date_default_timezone_set("Europe/London");'));

        return new CodePatcher($c->get(Parser::class), new Standard(), $c->get(LoggerInterface::class), $patch);
    },
    FakerGenerator::class => function () {
        return FakerFactory::create();
    },
    RequestRenderer::class => create(),

    Terminal::class => factory([TerminalFactory::class, 'fromSystem']),
    'menu' => factory(MenuFactory::class),
    MenuFactory::class => create(),
    ExerciseRenderer::class => function (ContainerInterface $c) {
        return new ExerciseRenderer(
            $c->get('appName'),
            $c->get(ExerciseRepository::class),
            $c->get(UserState::class),
            $c->get(Serializer::class),
            $c->get(MarkdownRenderer::class),
            $c->get(Color::class),
            $c->get(OutputInterface::class)
        );
    },
    ContextSpecificRenderer::class => function (ContainerInterface $c) {
        return new ContextSpecificRenderer($c->get(CurrentContext::class));
    },
    Context::class => function (ContainerInterface $c) {
        return new Context($c->get(CurrentContext::class));
    },
    CurrentContext::class => function () {
        return CurrentContext::cli();
    },
    Environment::class => function (ContainerInterface $c) {
        $terminal = $c->get(Terminal::class);

        $environment = new Environment([
            'renderer' => [
               'width' => $terminal->getWidth()
            ],
        ]);

        $environment
            ->addExtension(new CliExtension())
            ->addExtension(new ProblemFileExtension(
                $c->get(ContextSpecificRenderer::class),
                [
                    new AppName($c->get('appName')),
                    new Documentation(),
                    new Run($c->get('appName')),
                    new Verify($c->get('appName')),
                    $c->get(Context::class)
                ]
            ));

        return $environment;
    },
    MarkdownRenderer::class => function (ContainerInterface $c) {
        return new MarkdownRenderer(
            new DocParser($c->get(Environment::class)),
            $c->get(ElementRendererInterface::class)
        );
    },
    ElementRendererInterface::class => function (ContainerInterface $c) {
        return new CliRenderer(
            $c->get(Environment::class),
            $c->get(Color::class)
        );
    },
    Serializer::class => function (ContainerInterface $c) {
        return new LocalJsonSerializer(
            getenv('HOME'),
            $c->get('workshopTitle'),
            $c->get(ExerciseRepository::class)
        );
    },
    UserState::class => function (ContainerInterface $c) {
        return $c->get(Serializer::class)->deSerialize();
    },
    ResetProgress::class => function (ContainerInterface $c) {
        return new ResetProgress($c->get(Serializer::class));
    },
    ResultRendererFactory::class => function (ContainerInterface $c) {
        $factory = new ResultRendererFactory;
        $factory->registerRenderer(FunctionRequirementsFailure::class, FunctionRequirementsFailureRenderer::class);
        $factory->registerRenderer(Failure::class, FailureRenderer::class);
        $factory->registerRenderer(
            CgiResult::class,
            CgiResultRenderer::class,
            function (CgiResult $result) use ($c) {
                return new CgiResultRenderer($result, $c->get(RequestRenderer::class));
            }
        );
        $factory->registerRenderer(CgiGenericFailure::class, FailureRenderer::class);
        $factory->registerRenderer(CgiRequestFailure::class, CgiRequestFailureRenderer::class);

        $factory->registerRenderer(CliResult::class, CliResultRenderer::class);
        $factory->registerRenderer(CliGenericFailure::class, FailureRenderer::class);
        $factory->registerRenderer(CliRequestFailure::class, CliRequestFailureRenderer::class);

        $factory->registerRenderer(ComparisonFailure::class, ComparisonFailureRenderer::class);
        $factory->registerRenderer(FileComparisonFailure::class, FileComparisonFailureRenderer::class);
        $factory->registerRenderer(ComposerFailure::class, ComposerFailureRenderer::class);

        return $factory;
    },
    ResultsRenderer::class => function (ContainerInterface $c) {
        return new ResultsRenderer(
            $c->get('appName'),
            $c->get(Color::class),
            $c->get(Terminal::class),
            $c->get(ExerciseRepository::class),
            $c->get(KeyLighter::class),
            $c->get(ResultRendererFactory::class)
        );
    },

    KeyLighter::class => function () {
        $keylighter = new KeyLighter;
        $keylighter->init();
        return $keylighter;
    },

    'coreContributors' => [
        '@AydinHassan' => 'Aydin Hassan',
        '@mikeymike'   => 'Michael Woodward',
        '@shakeyShane' => 'Shane Osbourne',
        '@chris3ailey' => 'Chris Bailey'
    ],
    'appContributors' => [],
    'eventListeners'  => [
        'realpath-student-submission' => [
            'verify.start' => [
                containerListener(RealPathListener::class)
            ],
            'run.start' => [
                containerListener(RealPathListener::class)
            ],
        ],
        'check-exercise-assigned' => [
            'route.pre.resolve.args' => [
                containerListener(CheckExerciseAssignedListener::class)
            ],
        ],
        'configure-command-arguments' => [
            'route.pre.resolve.args' => [
                containerListener(ConfigureCommandListener::class)
            ],
        ],
        'prepare-solution' => [
            'cli.verify.start' => [
                containerListener(PrepareSolutionListener::class),
            ],
            'cli.run.start' => [
                containerListener(PrepareSolutionListener::class),
            ],
            'cgi.verify.start' => [
                containerListener(PrepareSolutionListener::class),
            ],
            'cgi.run.start' => [
                containerListener(PrepareSolutionListener::class),
            ],
        ],
        'code-patcher' => [
            'verify.pre.execute' => [
                containerListener(CodePatchListener::class, 'patch'),
            ],
            'verify.post.execute' => [
                containerListener(CodePatchListener::class, 'revert'),
            ],
            'run.start' => [
                containerListener(CodePatchListener::class, 'patch'),
            ],
            'run.finish' => [
                containerListener(CodePatchListener::class, 'revert'),
            ],
            'application.tear-down' => [
                containerListener(CodePatchListener::class, 'revert'),
            ],
        ],
        'self-check' => [
            'verify.post.check' => [
                containerListener(SelfCheckListener::class)
            ],
        ],
        'create-initial-code' => [
            'exercise.selected' => [
                containerListener(InitialCodeListener::class)
            ]
        ],
        'cleanup-filesystem' => [
            'application.tear-down' => [
                containerListener(TearDownListener::class, 'cleanupTempDir')
            ]
        ],
        'decorate-run-output' => [
            'cli.run.student-execute.pre' => [
                containerListener(OutputRunInfoListener::class)
            ],
            'cgi.run.student-execute.pre' => [
                containerListener(OutputRunInfoListener::class)
            ]
        ],
    ],
];
