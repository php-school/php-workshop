<?php

ini_set('display_errors', 1);
require_once 'vendor/autoload.php';

use AydinHassan\CliMdRenderer\CliRenderer;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;

$container = require_once __DIR__ . '/app/bootstrap.php';
$c = new \Colors\Color();
$c->setForceStyle(true);

$parser = $container->get(DocParser::class);
$exercises = glob('res/problems/*');
$numExercises = count($exercises);

$cliRender = $container->get(CLiRenderer::class);

$helpLine = function ($text, $cmd) use ($c) {
    $cmd = $c(sprintf('php %s %s', $_SERVER['argv'][0], $cmd))->yellow()->__toString();
    return sprintf(
        " %s %s: %s\n",
        $c("»")->bold()->__toString(),
        $text,
        $cmd
    );
};

$output = function ($exercise, $index) use ($c, $parser, $numExercises, $cliRender, $helpLine) {
    $title = strtoupper(str_replace("-", ' ', basename($exercise)));
    $content = file_get_contents($exercise . "/problem.md");

    echo "\n";
    echo $c(' LEARN YOU THE PHP FOR MUCH WIN! ')->green()->bold() . "\n";
    echo $c('─────────────────────────────────')->green()->bold() . "\n";
    echo $c(" $title")->yellow()->bold() . "\n";
    echo $c(" Exercise $index of $numExercises")->yellow() . "\n\n";

    $ast = $parser->parse($content);
    $doc = $cliRender->renderBlock($ast);
    $doc = str_replace('{appname}', $_SERVER['argv'][0], $doc);
    echo $doc;

    echo "\n";
    echo $helpLine('To print these instructions again, run', 'print');
    echo $helpLine('To execute your program in a test environment, run', 'run program.php');
    echo $helpLine('To verify your program, run', 'verify program.php');
    echo $helpLine('For help run', 'help');
    echo "\n\n";

};

foreach ($exercises as $index => $exercise) {
    $output($exercise, $index);
}