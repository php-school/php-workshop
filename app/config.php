<?php

use Colors\Color;
use function DI\object;
use function DI\factory;
use Interop\Container\ContainerInterface;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\CliMenuBuilder;
use PhpSchool\CliMenu\MenuItem\AsciiArtItem;
use PhpSchool\CliMenu\Terminal\TerminalFactory;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpSchool\PhpWorkshop\Result\CgiOutFailure;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;
use PhpSchool\PhpWorkshop\ResultRenderer\CgiOutFailureRenderer;
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
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\FunctionRequirementsExerciseCheck;
use PhpSchool\PhpWorkshop\ExerciseCheck\StdOutExerciseCheck;
use PhpSchool\PhpWorkshop\ExerciseRenderer;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\ExerciseRunner;
use PhpSchool\PhpWorkshop\Factory\MarkdownCliRendererFactory;
use PhpSchool\PhpWorkshop\MarkdownRenderer;
use PhpSchool\PhpWorkshop\Output;
use PhpSchool\PhpWorkshop\Result\FunctionRequirementsFailure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\ResultRenderer\FailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\FunctionRequirementsFailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultsRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\StdOutFailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\SuccessRenderer;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshop\UserStateSerializer;
use Symfony\Component\Filesystem\Filesystem;

return [
    'appName' => $_SERVER['argv'][0],
    ExerciseRunner::class => factory(function (ContainerInterface $c) {
        $exerciseRunner = new ExerciseRunner;
        foreach ($c->get('checks') as $check) {
            $exerciseRunner->registerCheck(...$check);
        }
        return $exerciseRunner;
    }),
    'checks' => factory(function (ContainerInterface $c) {
        return [
            [$c->get(FileExistsCheck::class), ExerciseInterface::class],
            [$c->get(PhpLintCheck::class), ExerciseInterface::class],
            [$c->get(StdOutCheck::class), StdOutExerciseCheck::class],
            [$c->get(FunctionRequirementsCheck::class), FunctionRequirementsExerciseCheck::class],
        ];
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
    Output::class => factory(function (ContainerInterface $c) {
        return new Output($c->get(Color::class));
    }),

    ExerciseRepository::class => factory(function (ContainerInterface $c) {
        return new ExerciseRepository(
            array_map(function ($exerciseClass) use ($c) {
                return $c->get($exerciseClass);
            }, $c->get('exercises'))
        );
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
            $c->get(Output::class)
        );
    }),

    VerifyCommand::class => factory(function (ContainerInterface $c) {
        return new VerifyCommand(
            $c->get(ExerciseRepository::class),
            $c->get(ExerciseRunner::class),
            $c->get(UserState::class),
            $c->get(UserStateSerializer::class),
            $c->get(Output::class),
            $c->get(ResultsRenderer::class)
        );
    }),
    
    CreditsCommand::class => factory(function (ContainerInterface $c) {
        return new CreditsCommand(
            $c->get(Output::class),
            $c->get(Color::class)
        );
    }),

    HelpCommand::class => factory(function (ContainerInterface $c) {
        return new HelpCommand(
            $c->get('appName'),
            $c->get(Output::class),
            $c->get(Color::class)
        );
    }),
    
    //checks
    FileExistsCheck::class              => object(FileExistsCheck::class),
    PhpLintCheck::class                 => object(PhpLintCheck::class),
    StdOutCheck::class                  => object(StdOutCheck::class),
    FunctionRequirementsCheck::class    => factory(function (ContainerInterface $c) {
        return new FunctionRequirementsCheck($c->get(Parser::class));
    }),

    //Utils
    Filesystem::class   => object(Filesystem::class),
    Parser::class       => factory(function (ContainerInterface $c) {
        $parserFactory = new ParserFactory;
        return $parserFactory->create(ParserFactory::PREFER_PHP7);
    }),
    
    TerminalInterface::class => factory([TerminalFactory::class, 'fromSystem']),
    'menu' => factory(function (ContainerInterface $c) {

        $userStateSerializer    = $c->get(UserStateSerializer::class);
        $exerciseRepository     = $c->get(ExerciseRepository::class);
        $userState              = $userStateSerializer->deSerialize();
        $exerciseRenderer       = $c->get(ExerciseRenderer::class);

        $builder = (new CliMenuBuilder)
            ->addLineBreak();

        if (null !== $c->get('workshopLogo')) {
            $builder->addAsciiArt($c->get('workshopLogo'), AsciiArtItem::POSITION_CENTER);
        }
        
        $builder
            ->addLineBreak('_')
            ->addLineBreak()
            ->addStaticItem('Exercises')
            ->addStaticItem('---------')
            ->addItems(array_map(function (ExerciseInterface $exercise) use ($exerciseRenderer, $userState) {
                return [
                    $exercise->getName(),
                    $exerciseRenderer,
                    $userState->completedExercise($exercise->getName())
                ];
            }, $exerciseRepository->findAll()))
            ->addLineBreak()
            ->addLineBreak('-')
            ->addLineBreak()
            ->addItem('HELP', function (CliMenu $menu) use ($c) {
                $menu->close();
                $c->get(HelpCommand::class)->__invoke();
            })
            ->addItem('CREDITS', function (CliMenu $menu) use ($c) {
                $menu->close();
                $c->get(CreditsCommand::class)->__invoke();
            })
            ->setExitButtonText('EXIT')
            ->setBackgroundColour($c->get('bgColour'))
            ->setForegroundColour($c->get('fgColour'))
            ->setWidth(70)
            ->setUnselectedMarker(' ')
            ->setSelectedMarker('↳')
            ->setItemExtra('[COMPLETED]');
            
        $subMenu = $builder
                ->addSubMenu('OPTIONS')
                ->addLineBreak();

        if (null !== $c->get('workshopLogo')) {
            $subMenu->addAsciiArt($c->get('workshopLogo'), AsciiArtItem::POSITION_CENTER);
        }

        $subMenu
            ->addLineBreak('_')
            ->addLineBreak()
            ->addStaticItem('Options')
            ->addStaticItem('-------')
            ->addItem(
                'Reset workshop progress',
                function (CliMenu $menu) use ($userStateSerializer) {
                    $userStateSerializer->serialize(new UserState);
                    echo "Status Reset!";
                }
            )
            ->addLineBreak()
            ->addLineBreak('-')
            ->addLineBreak()
            ->setGoBackButtonText('GO BACK')
            ->setExitButtonText('EXIT');
    
        if (null !== $c->get('workshopTitle')) {
            $builder->setTitle($c->get('workshopTitle'));
            $subMenu->setTitle($c->get('workshopTitle'));
        }
        
        return $builder->build();
    }),
    ExerciseRenderer::class => factory(function (ContainerInterface $c) {
        return new ExerciseRenderer(
            $c->get('appName'),
            $c->get(ExerciseRepository::class),
            $c->get(UserState::class),
            $c->get(UserStateSerializer::class),
            $c->get(MarkdownRenderer::class),
            $c->get(Color::class),
            $c->get(Output::class)
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
    ResultsRenderer::class => factory(function (ContainerInterface $c) {
        $renderer = new ResultsRenderer(
            $c->get('appName'),
            $c->get(Color::class),
            $c->get(TerminalInterface::class),
            $c->get(ExerciseRepository::class),
            $c->get(SyntaxHighlighter::class)
        );
        
        foreach ($c->get('renderers') as $resultRenderer) {
            $renderer->registerRenderer(...$resultRenderer);
        }
        return $renderer;
    }),
    'renderers' => factory(function (ContainerInterface $c) {
        
        return [
            [StdOutFailure::class, new OutputFailureRenderer],
            [CgiOutFailure::class, new CgiOutFailureRenderer()],
            [FunctionRequirementsFailure::class, new FunctionRequirementsFailureRenderer],
            [Success::class, new SuccessRenderer],
            [Failure::class, new FailureRenderer],
        ];
    }),
];
