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
use PhpSchool\PSX\SyntaxHighlighter;
use PhpWorkshop\PhpWorkshop\Check\FileExistsCheck;
use PhpWorkshop\PhpWorkshop\Check\FunctionRequirementsCheck;
use PhpWorkshop\PhpWorkshop\Check\PhpLintCheck;
use PhpWorkshop\PhpWorkshop\Check\StdOutCheck;
use PhpWorkshop\PhpWorkshop\Command\CreditsCommand;
use PhpWorkshop\PhpWorkshop\Command\HelpCommand;
use PhpWorkshop\PhpWorkshop\Command\MenuCommand;
use PhpWorkshop\PhpWorkshop\Command\PrintCommand;
use PhpWorkshop\PhpWorkshop\Command\VerifyCommand;
use PhpWorkshop\PhpWorkshop\CommandDefinition;
use PhpWorkshop\PhpWorkshop\CommandRouter;
use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\ExerciseCheck\FunctionRequirementsExerciseCheck;
use PhpWorkshop\PhpWorkshop\ExerciseCheck\StdOutExerciseCheck;
use PhpWorkshop\PhpWorkshop\ExerciseRenderer;
use PhpWorkshop\PhpWorkshop\ExerciseRepository;
use PhpWorkshop\PhpWorkshop\ExerciseRunner;
use PhpWorkshop\PhpWorkshop\Factory\MarkdownCliRendererFactory;
use PhpWorkshop\PhpWorkshop\MarkdownRenderer;
use PhpWorkshop\PhpWorkshop\Output;
use PhpWorkshop\PhpWorkshop\Result\FunctionRequirementsFailure;
use PhpWorkshop\PhpWorkshop\Result\Success;
use PhpWorkshop\PhpWorkshop\Result\Failure;
use PhpWorkshop\PhpWorkshop\ResultRenderer\FailureRenderer;
use PhpWorkshop\PhpWorkshop\ResultRenderer\FunctionRequirementsFailureRenderer;
use PhpWorkshop\PhpWorkshop\ResultRenderer\ResultsRenderer;
use PhpWorkshop\PhpWorkshop\ResultRenderer\StdOutFailureRenderer;
use PhpWorkshop\PhpWorkshop\ResultRenderer\SuccessRenderer;
use PhpWorkshop\PhpWorkshop\UserState;
use PhpWorkshop\PhpWorkshop\UserStateSerializer;
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
        
        foreach ($c->get('renderers') as $resultRenderer => $resultClass) {
            $renderer->registerRenderer($resultClass, $resultRenderer);
        }
        return $renderer;
    }),
    'renderers' => factory(function (ContainerInterface $c) {
        return [
            [new StdOutFailureRenderer, StdOutFailureRenderer::class],
            [new FunctionRequirementsFailureRenderer, FunctionRequirementsFailure::class],
            [new SuccessRenderer, Success::class],
            [new FailureRenderer, Failure::class],
        ];
    }),
];
