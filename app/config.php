<?php

use Colors\Color;
use function DI\object;
use function DI\factory;
use Interop\Container\ContainerInterface;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use PhpParser\PrettyPrinter\Standard;
use PhpSchool\CliMenu\Terminal\TerminalFactory;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpSchool\PhpWorkshop\Check\CheckRepository;
use PhpSchool\PhpWorkshop\Check\CodeParseCheck;
use PhpSchool\PhpWorkshop\Check\ComposerCheck;
use PhpSchool\PhpWorkshop\Check\DatabaseCheck;
use PhpSchool\PhpWorkshop\CodeInsertion as Insertion;
use PhpSchool\PhpWorkshop\CodePatcher;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Factory\EventDispatcherFactory;
use PhpSchool\PhpWorkshop\Factory\MenuFactory;
use PhpSchool\PhpWorkshop\Factory\ResultRendererFactory;
use PhpSchool\PhpWorkshop\Factory\RunnerFactory;
use PhpSchool\PhpWorkshop\Listener\CodePatchListener;
use PhpSchool\PhpWorkshop\Listener\PrepareSolutionListener;
use PhpSchool\PhpWorkshop\Listener\SelfCheckListener;
use PhpSchool\PhpWorkshop\MenuItem\ResetProgress;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use PhpSchool\PhpWorkshop\Patch;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PSX\Factory as PsxFactory;
use PhpSchool\PhpWorkshop\WorkshopType;
use PhpSchool\PSX\SyntaxHighlighter;
use PhpSchool\PhpWorkshop\Check\FileExistsCheck;
use PhpSchool\PhpWorkshop\Check\FunctionRequirementsCheck;
use PhpSchool\PhpWorkshop\Check\PhpLintCheck;
use PhpSchool\PhpWorkshop\Command\CreditsCommand;
use PhpSchool\PhpWorkshop\Command\HelpCommand;
use PhpSchool\PhpWorkshop\Command\MenuCommand;
use PhpSchool\PhpWorkshop\Command\PrintCommand;
use PhpSchool\PhpWorkshop\Command\VerifyCommand;
use PhpSchool\PhpWorkshop\Command\RunCommand;
use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\CommandRouter;
use PhpSchool\PhpWorkshop\ExerciseRenderer;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Factory\MarkdownCliRendererFactory;
use PhpSchool\PhpWorkshop\MarkdownRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultsRenderer;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshop\UserStateSerializer;
use Symfony\Component\Filesystem\Filesystem;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;

return [
    'appName' => basename($_SERVER['argv'][0]),
    WorkshopType::class => WorkshopType::STANDARD(),
    ExerciseDispatcher::class => function (ContainerInterface $c) {
        $dispatcher = new ExerciseDispatcher(
            $c->get(RunnerFactory::class),
            $c->get(ResultAggregator::class),
            $c->get(EventDispatcher::class),
            $c->get(CheckRepository::class)
        );

        //checks which should always run (probably)
        $dispatcher->requireCheck(FileExistsCheck::class);
        $dispatcher->requireCheck(PhpLintCheck::class);
        $dispatcher->requireCheck(CodeParseCheck::class);
        return $dispatcher;
    },
    ResultAggregator::class => object(ResultAggregator::class),
    CheckRepository::class => function (ContainerInterface $c) {
        return new CheckRepository([
            $c->get(FileExistsCheck::class),
            $c->get(PhpLintCheck::class),
            $c->get(CodeParseCheck::class),
            $c->get(ComposerCheck::class),
            $c->get(FunctionRequirementsCheck::class),
            $c->get(DatabaseCheck::class),
        ]);
    },
    CommandRouter::class => function (ContainerInterface $c) {
        return new CommandRouter(
            [
                new CommandDefinition('menu', [], MenuCommand::class),
                new CommandDefinition('help', [], HelpCommand::class),
                new CommandDefinition('print', [], PrintCommand::class),
                new CommandDefinition('verify', ['program'], VerifyCommand::class),
                new CommandDefinition('run', ['program'], RunCommand::class),
                new CommandDefinition('credits', [], CreditsCommand::class)
            ],
            'menu',
            $c
        );
    },

    Color::class => function () {
        $colors = new Color;
        $colors->setForceStyle(true);
        return $colors;
    },
    OutputInterface::class => function (ContainerInterface $c) {
        return new StdOutput($c->get(Color::class), $c->get(TerminalInterface::class));
    },

    ExerciseRepository::class => function (ContainerInterface $c) {
        return new ExerciseRepository(
            array_map(function ($exerciseClass) use ($c) {
                return $c->get($exerciseClass);
            }, $c->get('exercises'))
        );
    },

    EventDispatcher::class => factory(EventDispatcherFactory::class),
    EventDispatcherFactory::class => object(),

    //Exercise Runners
    RunnerFactory::class => object(),

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
            $c->get(UserStateSerializer::class),
            $c->get(OutputInterface::class),
            $c->get(ResultsRenderer::class)
        );
    },

    RunCommand::class => function (ContainerInterface $c) {
        return new RunCommand(
            $c->get(ExerciseRepository::class),
            $c->get(ExerciseDispatcher::class),
            $c->get(UserState::class),
            $c->get(UserStateSerializer::class),
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
    PrepareSolutionListener::class      => object(),
    CodePatchListener::class            => function (ContainerInterface $c) {
        return new CodePatchListener($c->get(CodePatcher::class));
    },
    SelfCheckListener::class            => function (ContainerInterface $c) {
        return new SelfCheckListener($c->get(ResultAggregator::class));
    },
    
    //checks
    FileExistsCheck::class              => object(),
    PhpLintCheck::class                 => object(),
    CodeParseCheck::class               => function (ContainerInterface $c) {
        return new CodeParseCheck($c->get(Parser::class));
    },
    FunctionRequirementsCheck::class    => function (ContainerInterface $c) {
        return new FunctionRequirementsCheck($c->get(Parser::class));
    },
    DatabaseCheck::class                => object(),
    ComposerCheck::class                => object(),

    //Utils
    Filesystem::class   => object(),
    Parser::class       => function () {
        $parserFactory = new ParserFactory;
        return $parserFactory->create(ParserFactory::PREFER_PHP7);
    },
    CodePatcher::class  => function (ContainerInterface $c) {
        $patch = (new Patch)
            ->withInsertion(new Insertion(Insertion::TYPE_BEFORE, 'ini_set("display_errors", 1);'))
            ->withInsertion(new Insertion(Insertion::TYPE_BEFORE, 'error_reporting(E_ALL);'))
            ->withInsertion(new Insertion(Insertion ::TYPE_BEFORE, 'date_default_timezone_set("Europe/London");'));
        
        return new CodePatcher($c->get(Parser::class), new Standard, $patch);
    },
    FakerGenerator::class => function () {
        return FakerFactory::create();
    },
    
    TerminalInterface::class => factory([TerminalFactory::class, 'fromSystem']),
    'menu' => factory(MenuFactory::class),
    MenuFactory::class => object(),
    ExerciseRenderer::class => function (ContainerInterface $c) {
        return new ExerciseRenderer(
            $c->get('appName'),
            $c->get(ExerciseRepository::class),
            $c->get(UserState::class),
            $c->get(UserStateSerializer::class),
            $c->get(MarkdownRenderer::class),
            $c->get(Color::class),
            $c->get(OutputInterface::class)
        );
    },
    MarkdownRenderer::class => function (ContainerInterface $c) {
        $docParser =   new DocParser(Environment::createCommonMarkEnvironment());
        $cliRenderer = (new MarkdownCliRendererFactory)->__invoke($c);
        return new MarkdownRenderer($docParser, $cliRenderer);
    },
    UserStateSerializer::class => function (ContainerInterface $c) {
        return new UserStateSerializer(
            getenv('HOME'),
            $c->get('workshopTitle'),
            $c->get(ExerciseRepository::class)
        );
    },
    UserState::class => function (ContainerInterface $c) {
        return $c->get(UserStateSerializer::class)->deSerialize();
    },
    SyntaxHighlighter::class => factory(PsxFactory::class),
    PsxFactory::class => object(),
    ResetProgress::class => function (ContainerInterface $c) {
        return new ResetProgress(
            $c->get(UserStateSerializer::class),
            $c->get(OutputInterface::class)
        );
    },
    ResultRendererFactory::class => object(),
    ResultsRenderer::class => function (ContainerInterface $c) {
        return new ResultsRenderer(
            $c->get('appName'),
            $c->get(Color::class),
            $c->get(TerminalInterface::class),
            $c->get(ExerciseRepository::class),
            $c->get(SyntaxHighlighter::class),
            $c->get(ResultRendererFactory::class)
        );
    },
    'coreContributors' => [
        '@AydinHassan' => 'Aydin Hassan',
        '@mikeymike'   => 'Michael Woodward',
        '@shakeyShane' => 'Shane Osbourne',
        '@chris3ailey' => 'Chris Bailey'
    ],
    'appContributors' => []
];
