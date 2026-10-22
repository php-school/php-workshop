<?php

namespace PhpSchool\PhpWorkshopTest;

use League\CommonMark\Parser\MarkdownParser;
use PhpSchool\PhpWorkshop\Factory\MarkdownEnvironmentFactory;
use PhpSchool\PhpWorkshop\Markdown\Renderer\ContextSpecificRenderer;
use PhpSchool\CliMdRenderer\CliRenderer;
use Colors\Color;
use PhpSchool\PhpWorkshop\Markdown\CurrentContext;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\MenuItem\MenuItemInterface;
use PhpSchool\PhpWorkshop\UserState\Serializer;
use PhpSchool\PhpWorkshop\UserState\UserState;
use PhpSchool\Terminal\Terminal;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRenderer;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\MarkdownRenderer;
use PhpSchool\PhpWorkshop\Output\StdOutput;

class ExerciseRendererTest extends TestCase
{
    public function testExerciseRendererSetsCurrentExerciseAndRendersExercise(): void
    {
        $menu = $this->createMock(CliMenu::class);

        $item = $this->createMock(MenuItemInterface::class);
        $item
            ->method('getText')
            ->willReturn('Exercise 2');
        $menu
            ->expects($this->once())
            ->method('getSelectedItem')
            ->willReturn($item);

        $exercise1 = $this->createMock(ExerciseInterface::class);
        $exercise2 = $this->createMock(ExerciseInterface::class);
        $exercises = [$exercise1, $exercise2];
        $exerciseRepository = $this->createMock(ExerciseRepository::class);
        $userState = $this->createMock(UserState::class);
        $userStateSerializer = $this->createMock(Serializer::class);

        $exerciseRepository
            ->expects($this->once())
            ->method('findByName')
            ->with('Exercise 2')
            ->willReturn($exercise2);

        $exerciseRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($exercises);

        $userState
            ->expects($this->once())
            ->method('setCurrentExercise')
            ->with('Exercise 2');

        $userStateSerializer
            ->expects($this->once())
            ->method('serialize')
            ->with($userState);
        $problemFile = sprintf('%s/%s/problem.md', sys_get_temp_dir(), $this->name());
        $exercise2
            ->expects($this->once())
            ->method('getProblem')
            ->willReturn($problemFile);

        if (!is_dir(dirname($problemFile))) {
            mkdir(dirname($problemFile), 0775, true);
        }
        file_put_contents($problemFile, '### Exercise Content');

        $color = new Color();
        $color->setForceStyle(true);

        $markdownRenderer = new MarkdownRenderer(
            new MarkdownParser((new MarkdownEnvironmentFactory())->create(80, new ContextSpecificRenderer(CurrentContext::cli()), [])),
            new CliRenderer((new MarkdownEnvironmentFactory())->create(80, new ContextSpecificRenderer(CurrentContext::cli()), []), $color),
        );

        $exerciseRenderer = new ExerciseRenderer(
            'phpschool',
            $exerciseRepository,
            $userState,
            $userStateSerializer,
            $markdownRenderer,
            $color,
            new StdOutput($color, $this->createMock(Terminal::class)),
        );

        $this->expectOutputString(file_get_contents(__DIR__ . '/res/exercise-help-expected.txt'));
        $exerciseRenderer->__invoke($menu);

        unlink($problemFile);
    }
}
