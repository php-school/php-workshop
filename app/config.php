<?php

use AydinHassan\CliMdRenderer\CliRenderer;
use AydinHassan\CliMdRenderer\Highlighter\PhpHighlighter;
use AydinHassan\CliMdRenderer\InlineRenderer\CodeRenderer;
use AydinHassan\CliMdRenderer\InlineRenderer\EmphasisRenderer;
use AydinHassan\CliMdRenderer\InlineRenderer\LinkRenderer;
use AydinHassan\CliMdRenderer\InlineRenderer\NewlineRenderer;
use AydinHassan\CliMdRenderer\InlineRenderer\StrongRenderer;
use AydinHassan\CliMdRenderer\InlineRenderer\TextRenderer;
use AydinHassan\CliMdRenderer\Renderer\DocumentRenderer;
use AydinHassan\CliMdRenderer\Renderer\FencedCodeRenderer;
use AydinHassan\CliMdRenderer\Renderer\HeaderRenderer;
use AydinHassan\CliMdRenderer\Renderer\HorizontalRuleRenderer;
use AydinHassan\CliMdRenderer\Renderer\ParagraphRenderer;
use Colors\Color;
use function DI\object;
use function DI\factory;
use Faker\Factory as FakerFactory;
use Interop\Container\ContainerInterface;
use League\CommonMark\Block\Element\Document;
use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\Block\Element\Header;
use League\CommonMark\Block\Element\HorizontalRule;
use League\CommonMark\Block\Element\Paragraph;
use League\CommonMark\DocParser;
use League\CommonMark\Inline\Element\Code;
use League\CommonMark\Inline\Element\Emphasis;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Element\Newline;
use League\CommonMark\Inline\Element\Strong;
use League\CommonMark\Inline\Element\Text;
use MikeyMike\CliMenu\CliMenu;
use MikeyMike\CliMenu\MenuItem\MenuItem;
use MikeyMike\CliMenu\Terminal\TerminalFactory;
use MikeyMike\CliMenu\Terminal\TerminalInterface;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpSchool\PSX\Factory as PSXFactory;
use PhpWorkshop\PhpWorkshop\Check\CheckInterface;
use PhpWorkshop\PhpWorkshop\Check\FileExistsCheck;
use PhpWorkshop\PhpWorkshop\Check\FunctionRequirementsCheck;
use PhpWorkshop\PhpWorkshop\Check\PhpLintCheck;
use PhpWorkshop\PhpWorkshop\Check\StdOutCheck;
use PhpWorkshop\PhpWorkshop\Exercise\BabySteps;
use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\Exercise\FilteredLs;
use PhpWorkshop\PhpWorkshop\Exercise\HelloWorld;
use PhpWorkshop\PhpWorkshop\Exercise\MyFirstIo;
use PhpWorkshop\PhpWorkshop\ExerciseCheck\FunctionRequirementsExerciseCheck;
use PhpWorkshop\PhpWorkshop\ExerciseCheck\StdOutExerciseCheck;
use PhpWorkshop\PhpWorkshop\ExerciseRunner;
use Symfony\Component\Filesystem\Filesystem;

return [
    ExerciseRunner::class => factory(function (ContainerInterface $c) {
        $exerciseRunner = new ExerciseRunner;

        foreach ($c->get('checks') as $check) {
            list($check, $exerciseInterface) = $check;
            $exerciseRunner->registerCheck($check, $exerciseInterface);
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
    'application' => factory(function (ContainerInterface $c) {
        //TODO this is where we would create some CLI Instance
        //which displays exercies
        //and parses args
        return $c->get(ExerciseRunner::class);
    }),

    //checks
    FileExistsCheck::class              => object(FileExistsCheck::class),
    PhpLintCheck::class                 => object(PhpLintCheck::class),
    StdOutCheck::class                  => object(StdOutCheck::class),
    FunctionRequirementsCheck::class    => factory(function (ContainerInterface $c) {
        return new FunctionRequirementsCheck($c->get(Parser::class));
    }),


    'exercises' => factory(function (ContainerInterface $c) {
        return [
            BabySteps::class,
            HelloWorld::class,
            MyFirstIo::class,
            FilteredLs::class,
        ];
    }),

    //Utils
    Filesystem::class   => object(Filesystem::class),
    Parser::class       => factory(function (ContainerInterface $c) {
        $parserFactory = new ParserFactory;
        return $parserFactory->create(ParserFactory::PREFER_PHP7);
    }),

    //Exercises
    BabySteps::class    => object(BabySteps::class),
    HelloWorld::class   => object(HelloWorld::class),
    MyFirstIo::class    => factory(function (ContainerInterface $c) {
        return new MyFirstIo($c->get(Filesystem::class), FakerFactory::create());
    }),
    FilteredLs::class   => factory(function (ContainerInterface $c) {
        return new FilteredLs($c->get(Filesystem::class));
    }),

    TerminalInterface::class => factory([TerminalFactory::class, 'fromSystem']),
    CliMenu::class => factory(function (ContainerInterface $c) {
        return new CliMenu(
            'Basic CLI Menu',
            array(
                new MenuItem('First Item'),
                new MenuItem('Second Item'),
                new MenuItem('Third Item')
            ),
            function (CliMenu $menu) {
                echo sprintf(
                    "\n%s\n",
                    $menu->getSelectedItem()->getText()
                );
            }
        );
    }),
    DocParser::class => factory(function (ContainerInterface $c) {
        return new DocParser(\League\CommonMark\Environment::createCommonMarkEnvironment());
    }),
    CliRenderer::class => factory(function (ContainerInterface $c) {
        $terminal = $c->get(TerminalInterface::class);

        $highlighterFactory = new PSXFactory;
        $codeRender = new FencedCodeRenderer();
        $codeRender->addSyntaxHighlighter('php', new PhpHighlighter($highlighterFactory->__invoke()));

        $blockRenderers = [
            Document::class         => new DocumentRenderer,
            Header::class           => new HeaderRenderer,
            HorizontalRule::class   => new HorizontalRuleRenderer($terminal->getWidth()),
            Paragraph::class        => new ParagraphRenderer,
            FencedCode::class       => $codeRender,
        ];

        $inlineBlockRenderers = [
            Text::class             => new TextRenderer,
            Code::class             => new CodeRenderer,
            Emphasis::class         => new EmphasisRenderer,
            Strong::class           => new StrongRenderer,
            Newline::class          => new NewlineRenderer,
            Link::class             => new LinkRenderer,
        ];

        $colors = new Color;
        $colors->setForceStyle(true);

        return new CliRenderer($blockRenderers, $inlineBlockRenderers, $colors);
    }),
];