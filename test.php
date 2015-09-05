<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpWorkshop\PhpWorkshop\Comparator\StdOut;
use PhpWorkshop\PhpWorkshop\Exercise\BabySteps;
use PhpWorkshop\PhpWorkshop\Exercise\HelloWorld;
use PhpWorkshop\PhpWorkshop\ExerciseRunner;

$runner = new ExerciseRunner(new StdOut);

echo "====HELLO WORLD====\n\n";
$helloWorld = new HelloWorld;

echo "____FAIL___\n\n";
$result = $runner->runExercise($helloWorld, __DIR__ . '/test/res/hello-world-fail.php');

echo "Successful?\n";
var_dump($result->isSuccessful());

echo "Errors\n";
var_dump($result->getErrors());

echo "____PASS___\n\n";
$result = $runner->runExercise($helloWorld, __DIR__ . '/test/res/hello-world-pass.php');

echo "Successful?\n";
var_dump($result->isSuccessful());

echo "Errors\n";
var_dump($result->getErrors());

echo "====HELLO WORLD====\n\n";
$babySteps = new BabySteps;

echo "____FAIL___\n\n";
$result = $runner->runExercise($babySteps, __DIR__ . '/test/res/baby-steps-fail.php');

echo "Successful?\n";
var_dump($result->isSuccessful());

echo "Errors\n";
var_dump($result->getErrors());

echo "____PASS___\n\n";
$result = $runner->runExercise($babySteps, __DIR__ . '/test/res/baby-steps-pass.php');

echo "Successful?\n";
var_dump($result->isSuccessful());

echo "Errors\n";
var_dump($result->getErrors());
