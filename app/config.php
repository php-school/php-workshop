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
use PhpSchool\PhpWorkshop\Check\CgiOutputCheck;
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
use PhpSchool\PhpWorkshop\Result\CgiOutResult;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\ResultRenderer\CgiOutResultRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\OutputFailureRenderer;
use PhpSchool\PSX\SyntaxHighlighter;
use PhpSchool\PhpWorkshop\Check\FileExistsCheck;
use PhpSchool\PhpWorkshop\Check\FunctionRequirementsCheck;
use PhpSchool\PhpWorkshop\Check\PhpLintCheck;
use PhpSchool\PhpWorkshop\Check\StdOutCheck;
use PhpSchool\PhpWorkshop\Command\CreditsCommand;
use PhpSchool\PhpWorkshop\Command\HelpCommand;
use PhpSchool\PhpWorkshop\Command\MenuCommand;
use PhpSchool\PhpWorkshop\Command\PrintCommand;
use PhpSchool\PhpWorkshop\Command\VerifyCommand;
use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\CommandRouter;
use PhpSchool\PhpWorkshop\ExerciseCheck\StdOutExerciseCheck;
use PhpSchool\PhpWorkshop\ExerciseRenderer;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\ExerciseRunner;
use PhpSchool\PhpWorkshop\Factory\MarkdownCliRendererFactory;
use PhpSchool\PhpWorkshop\MarkdownRenderer;
use PhpSchool\PhpWorkshop\Result\FunctionRequirementsFailure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\ResultRenderer\FailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\FunctionRequirementsFailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultsRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\SuccessRenderer;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshop\UserStateSerializer;
use Symfony\Component\Filesystem\Filesystem;

return [
    'appName' => $_SERVER['argv'][0],
    ExerciseDispatcher::class => factory(function (ContainerInterface $c) {
        $dispatcher = new ExerciseDispatcher(
            $c->get(RunnerFactory::class),
            $c->get(ResultAggregator::class),
            $c->get(EventDispatcher::class),
            $c->get(CheckRepository::class)
        );

        //checks which should always run (probably)
        $dispatcher->requireCheck(FileExistsCheck::class, ExerciseDispatcher::CHECK_BEFORE);
        $dispatcher->requireCheck(PhpLintCheck::class, ExerciseDispatcher::CHECK_BEFORE);
        $dispatcher->requireCheck(CodeParseCheck::class, ExerciseDispatcher::CHECK_BEFORE);
        return $dispatcher;
    }),
    ResultAggregator::class => object(ResultAggregator::class),
    CheckRepository::class => factory(function (ContainerInterface $c) {
        return new CheckRepository([
            $c->get(FileExistsCheck::class),
            $c->get(PhpLintCheck::class),
            $c->get(CodeParseCheck::class),
            $c->get(ComposerCheck::class),
            $c->get(FunctionRequirementsCheck::class),
            $c->get(DatabaseCheck::class),
        ]);
    }),
    CommandRouter::class => factory(function (ContainerInterface $c) {
        return new CommandRouter(
            [
                new CommandDefinition('run', [], MenuCommand::class),
                new CommandDefinition('help', [], HelpCommand::class),
                new CommandDefinition('print', [], PrintCommand::class),
                new CommandDefinition('verify', ['program'], VerifyCommand::class),
                new CommandDefinition('credits', [], CreditsCommand::class)
            ],
            'run',
            $c
        );
    }),

    Color::class => factory(function (ContainerInterface $c) {
        $colors = new Color;
        $colors->setForceStyle(true);
        return $colors;
    }),
    OutputInterface::class => factory(function (ContainerInterface $c) {
        return new StdOutput($c->get(Color::class));
    }),

    ExerciseRepository::class => factory(function (ContainerInterface $c) {
        return new ExerciseRepository(
            array_map(function ($exerciseClass) use ($c) {
                return $c->get($exerciseClass);
            }, $c->get('exercises'))
        );
    }),

    EventDispatcher::class => factory([new EventDispatcherFactory, '__invoke']),

    //Exercise Runners
    RunnerFactory::class => factory(function (ContainerInterface $c) {
        return new RunnerFactory;
    }),

    //commands
    MenuCommand::class => factory(function (ContainerInterface $c) {
        return new MenuCommand($c->get('menu'));
    }),

    PrintCommand::class => factory(function (ContainerInterface $c) {
        return new PrintCommand(
            $c->get(ExerciseRepository::class),
            $c->get(UserState::class),
            $c->get(MarkdownRenderer::class),
            $c->get(OutputInterface::class)
        );
    }),

    VerifyCommand::class => factory(function (ContainerInterface $c) {
        return new VerifyCommand(
            $c->get(ExerciseRepository::class),
            $c->get(ExerciseDispatcher::class),
            $c->get(UserState::class),
            $c->get(UserStateSerializer::class),
            $c->get(OutputInterface::class),
            $c->get(ResultsRenderer::class)
        );
    }),
    
    CreditsCommand::class => factory(function (ContainerInterface $c) {
        return new CreditsCommand(
            $c->get('coreContributors'),
            $c->get('appContributors'),
            $c->get(OutputInterface::class),
            $c->get(Color::class)
        );
    }),

    HelpCommand::class => factory(function (ContainerInterface $c) {
        return new HelpCommand(
            $c->get('appName'),
            $c->get(OutputInterface::class),
            $c->get(Color::class)
        );
    }),

    //Listeners
    PrepareSolutionListener::class      => object(),
    CodePatchListener::class            => factory(function (ContainerInterface $c) {
        return new CodePatchListener($c->get(CodePatcher::class));
    }),
    SelfCheckListener::class            => factory(function (ContainerInterface $c) {
        return new SelfCheckListener($c->get(ResultAggregator::class));
    }),
    
    //checks
    FileExistsCheck::class              => object(FileExistsCheck::class),
    PhpLintCheck::class                 => object(PhpLintCheck::class),
    CodeParseCheck::class               => factory(function (ContainerInterface $c) {
        return new CodeParseCheck($c->get(Parser::class));
    }),
    StdOutCheck::class                  => object(StdOutCheck::class),
    FunctionRequirementsCheck::class    => factory(function (ContainerInterface $c) {
        return new FunctionRequirementsCheck($c->get(Parser::class));
    }),
    CgiOutputCheck::class               => object(CgiOutputCheck::class),
    DatabaseCheck::class                => object(DatabaseCheck::class),
    ComposerCheck::class                => object(ComposerCheck::class),

    //Utils
    Filesystem::class   => object(Filesystem::class),
    Parser::class       => factory(function (ContainerInterface $c) {
        $parserFactory = new ParserFactory;
        return $parserFactory->create(ParserFactory::PREFER_PHP7);
    }),
    CodePatcher::class  => factory(function (ContainerInterface $c) {
        $patch = (new Patch)
            ->withInsertion(new Insertion(Insertion::TYPE_BEFORE, 'ini_set("display_errors", 1);'))
            ->withInsertion(new Insertion(Insertion::TYPE_BEFORE, 'error_reporting(E_ALL);'))
            ->withInsertion(new Insertion(Insertion ::TYPE_BEFORE, 'date_default_timezone_set("Europe/London");'));
        
        return new CodePatcher($c->get(Parser::class), new Standard, $patch);
    }),
    
    TerminalInterface::class => factory([TerminalFactory::class, 'fromSystem']),
    'menu' => factory([new MenuFactory, '__invoke']),
    ExerciseRenderer::class => factory(function (ContainerInterface $c) {
        return new ExerciseRenderer(
            $c->get('appName'),
            $c->get(ExerciseRepository::class),
            $c->get(UserState::class),
            $c->get(UserStateSerializer::class),
            $c->get(MarkdownRenderer::class),
            $c->get(Color::class),
            $c->get(OutputInterface::class)
        );
    }),
    MarkdownRenderer::class => factory(function (ContainerInterface $c) {
        $docParser =   new DocParser(Environment::createCommonMarkEnvironment());
        $cliRenderer = (new MarkdownCliRendererFactory)->__invoke($c);
        return new MarkdownRenderer($docParser, $cliRenderer);
    }),
    UserStateSerializer::class => factory(function () {
        return new UserStateSerializer(sprintf('%s/.phpschool.json', getenv('HOME')));
    }),
    UserState::class => factory(function (ContainerInterface $c) {
        return $c->get(UserStateSerializer::class)->deSerialize();
    }),
    SyntaxHighlighter::class => factory(function (ContainerInterface $c) {
        return (new \PhpSchool\PSX\Factory)->__invoke();
    }),
    ResetProgress::class => factory(function (ContainerInterface $c) {
        return new ResetProgress(
            $c->get(UserStateSerializer::class),
            $c->get(OutputInterface::class)
        );
    }),
    ResultRendererFactory::class => object(),
    ResultsRenderer::class => factory(function (ContainerInterface $c) {
        return new ResultsRenderer(
            $c->get('appName'),
            $c->get(Color::class),
            $c->get(TerminalInterface::class),
            $c->get(ExerciseRepository::class),
            $c->get(SyntaxHighlighter::class),
            $c->get(ResultRendererFactory::class)
        );
    }),
    'coreContributors' => [
        '@AydinHassan' => 'Aydin Hassan',
        '@mikeymike'   => 'Michael Woodward',
        '@shakeyShane' => 'Shane Osbourne',
        '@chris3ailey' => 'Chris Bailey'
    ],
    'appContributors' => []
];
