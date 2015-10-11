<?php

namespace PhpWorkshop\PhpWorkshopTest;

use AydinHassan\CliMdRenderer\CliRendererFactory;
use Colors\Color;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use MikeyMike\CliMenu\CliMenu;
use MikeyMike\CliMenu\MenuItem\MenuItem;
use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\ExerciseRenderer;
use PhpWorkshop\PhpWorkshop\ExerciseRepository;
use PhpWorkshop\PhpWorkshop\MarkdownRenderer;
use PhpWorkshop\PhpWorkshop\Output;
use PhpWorkshop\PhpWorkshop\UserState;
use PhpWorkshop\PhpWorkshop\UserStateSerializer;

/**
 * Class ExerciseRendererTest
 * @package PhpWorkshop\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseRendererTest extends PHPUnit_Framework_TestCase
{
    public function testExerciseRendererSetsCurrentExerciseAndRendersExercise()
    {
        $menu = $this->getMockBuilder(CliMenu::class)
            ->disableOriginalConstructor()
            ->getMock();

        $item = new MenuItem('Exercise 2');
        $menu
            ->expects($this->once())
            ->method('getSelectedItem')
            ->will($this->returnValue($item));

        $menu
            ->expects($this->once())
            ->method('close');

        $exercise1 = $this->getMock(ExerciseInterface::class);
        $exercise2 = $this->getMock(ExerciseInterface::class);
        $exercises = [$exercise1, $exercise2];
        $exerciseRepository = $this->getMockBuilder(ExerciseRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userState = $this->getMock(UserState::class);
        $userStateSerializer = $this->getMockBuilder(UserStateSerializer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exerciseRepository
            ->expects($this->once())
            ->method('findByName')
            ->with('Exercise 2')
            ->will($this->returnValue($exercise2));

        $exerciseRepository
            ->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($exercises));

        $userState
            ->expects($this->once())
            ->method('setCurrentExercise')
            ->with('Exercise 2');

        $userStateSerializer
            ->expects($this->once())
            ->method('serialize')
            ->with($userState);
        $problemFile = sprintf('%s/%s/problem.md', sys_get_temp_dir(), $this->getName());
        $exercise2
            ->expects($this->once())
            ->method('getProblem')
            ->will($this->returnValue($problemFile));

        if (!is_dir(dirname($problemFile))) {
            mkdir(dirname($problemFile), 0775, true);
        }
        file_put_contents($problemFile, $this->getExerciseContent());

        $markdownRenderer = new MarkdownRenderer(
            new DocParser(Environment::createCommonMarkEnvironment()),
            (new CliRendererFactory)->__invoke()
        );

        $color = new Color;
        $color->setForceStyle(true);

        $exerciseRenderer = new ExerciseRenderer(
            'phpschool',
            $exerciseRepository,
            $userState,
            $userStateSerializer,
            $markdownRenderer,
            $color,
            new Output($color)
        );

        $this->expectOutputString($this->getExpected());
        $exerciseRenderer->__invoke($menu);

        unlink($problemFile);
    }

    /**
     * @return string
     */
    private function getExerciseContent()
    {
        return <<<EOT

Write a program that prints the text "HELLO WORLD" to the console (stdout).

----------------------------------------------------------------------
## HINTS

To make a PHP program, create a new file with a `.php` extension and start writing PHP! Execute your program by running it with the
`php` command. e.g.:

```sh
$ php program.php
```

You can write to the console like so:

```php
<?php

echo "text";
```

When you are done, you must run:

```sh
$ {appname} verify program.php
```

to proceed. Your program will be tested, a report will be generated, and the lesson will be marked 'completed' if you are successful.

----------------------------------------------------------------------
EOT;
    }

    /**
     * @return string
     */
    private function getExpected()
    {
        return <<<EOT

[1m[32m LEARN YOU THE PHP FOR MUCH WIN! [0m[0m
[1m[32m*********************************[0m[0m
[1m[33m [0m[0m
[33m Exercise 2 of 2

[0mWrite a program that prints the text "HELLO WORLD" to the console (stdout).

[90m------------------------------[0m

[90m##[0m [36m[1mHINTS[0m[0m

To make a PHP program, create a new file with a [33m.php[0m extension and start writing PHP! Execute your program by running it with the
[33mphp[0m command. e.g.:

    [33m$ php program.php
    [0m
You can write to the console like so:

    [36m<?php[0m
    
    [33mecho[0m [32m'text'[0m;
    
When you are done, you must run:

    [33m$ phpschool verify program.php
    [0m
to proceed. Your program will be tested, a report will be generated, and the lesson will be marked 'completed' if you are successful.

[90m------------------------------[0m

 [1m»[0m To print these instructions again, run: [33mphp phpschool print[0m
 [1m»[0m To execute your program in a test environment, run: [33mphp phpschool run program.php[0m
 [1m»[0m To verify your program, run: [33mphp phpschool verify program.php[0m
 [1m»[0m For help run: [33mphp phpschool help[0m



EOT;


        $content = "
[1m[32m LEARN YOU THE PHP FOR MUCH WIN! [0m[0m
[1m[32m*********************************[0m[0m
[1m[33m [0m[0m
[33m Exercise 2 of 2

[0mWrite a program that prints the text \"HELLO WORLD\" to the console (stdout).

[90m------------------------------[0m

[90m##[0m [36m[1mHINTS[0m[0m

To make a PHP program, create a new file with a [33m.php[0m extension and start writing PHP! Execute your program by running it with the
[33mphp[0m command. e.g.:

    [33m$ php program.php
    [0m
You can write to the console like so:

    [36m<?php[0m

    [33mecho[0m [32m'text'[0m;

When you are done, you must run:

    [33m$ phpschool verify program.php
    [0m
to proceed. Your program will be tested, a report will be generated, and the lesson will be marked 'completed' if you are successful.

[90m------------------------------[0m

 [1m»[0m To print these instructions again, run: [33mphp phpschool print[0m
 [1m»[0m To execute your program in a test environment, run: [33mphp phpschool run program.php[0m
 [1m»[0m To verify your program, run: [33mphp phpschool verify program.php[0m
 [1m»[0m For help run: [33mphp phpschool help[0m


";

        return $content;
    }
}
