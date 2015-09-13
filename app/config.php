<?php

use function DI\object;
use function DI\factory;
use Faker\Factory as FakerFactory;
use Interop\Container\ContainerInterface;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpWorkshop\PhpWorkshop\Check\CheckInterface;
use PhpWorkshop\PhpWorkshop\Check\FileExistsCheck;
use PhpWorkshop\PhpWorkshop\Check\FunctionRequirementsCheck;
use PhpWorkshop\PhpWorkshop\Check\PhpLintCheck;
use PhpWorkshop\PhpWorkshop\Check\StdOutCheck;
use PhpWorkshop\PhpWorkshop\Exercise\BabySteps;
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
            [$c->get(FileExistsCheck::class), CheckInterface::class],
            [$c->get(PhpLintCheck::class), CheckInterface::class],
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
        ];
    }),

    //Utils
    Filesystem::class   => object(Filesystem::class),
    Parser::class       => factory(function (ContainerInterface $c) {
       return new Parser(new Lexer\Emulative);
    }),

    //Exercises
    BabySteps::class    => object(BabySteps::class),
    HelloWorld::class   => object(HelloWorld::class),
    MyFirstIo::class    => factory(function (ContainerInterface $c) {
        return new MyFirstIo($c->get(Filesystem::class), FakerFactory::create());
    }),
];