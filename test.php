<?php

ini_set('display_errors', 1);

$container = require_once __DIR__ . '/app/bootstrap.php';
$runner = $container->get('application');

use PhpWorkshop\PhpWorkshop\Exercise\BabySteps;
use PhpWorkshop\PhpWorkshop\Exercise\HelloWorld;
use PhpWorkshop\PhpWorkshop\Exercise\MyFirstIo;
use PhpWorkshop\PhpWorkshop\Exercise\FilteredLs;

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

echo "====BABY STEPS====\n\n";
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

echo "====My First IO====\n\n";
$myFirstIo = $container->get(MyFirstIo::class);

echo "____FAIL___\n\n";
$result = $runner->runExercise($myFirstIo, __DIR__ . '/test/res/my-first-io-fail.php');

echo "Successful?\n";
var_dump($result->isSuccessful());

echo "Errors\n";
var_dump($result->getErrors());

echo "____PASS___\n\n";
$result = $runner->runExercise($myFirstIo, __DIR__ . '/test/res/my-first-io-pass.php');

echo "Successful?\n";
var_dump($result->isSuccessful());

echo "Errors\n";
var_dump($result->getErrors());

echo "====Filtered LS====\n\n";
$filteredLs = $container->get(FilteredLs::class);

echo "____FAIL___\n\n";
$result = $runner->runExercise($filteredLs, __DIR__ . '/test/res/filtered-ls-fail.php');

echo "Successful?\n";
var_dump($result->isSuccessful());

echo "Errors\n";
var_dump($result->getErrors());

echo "____PASS___\n\n";
$result = $runner->runExercise($filteredLs, __DIR__ . '/test/res/filtered-ls-pass.php');

echo "Successful?\n";
var_dump($result->isSuccessful());

echo "Errors\n";
var_dump($result->getErrors());

