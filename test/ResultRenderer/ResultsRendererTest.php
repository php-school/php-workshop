<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use Colors\Color;
use Kadet\Highlighter\Formatter\CliFormatter;
use Kadet\Highlighter\KeyLighter;
use Kadet\Highlighter\Language\Php;
use PhpSchool\PhpWorkshop\UserState\UserState;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PhpSchool\PhpWorkshopTest\BaseTest;
use PhpSchool\Terminal\Terminal;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Factory\ResultRendererFactory;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\ResultRenderer\FailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultsRenderer;
use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;

use function PhpSchool\PhpWorkshop\camel_case_to_kebab_case;

class ResultsRendererTest extends BaseTest
{
    public function testRenderIndividualResult(): void
    {
        $color = new Color();
        $color->setForceStyle(true);

        $resultRendererFactory = new ResultRendererFactory();
        $resultRendererFactory->registerRenderer(Failure::class, FailureRenderer::class);

        $terminal = $this->createMock(Terminal::class);
        $terminal->method('getWidth')->willReturn(30);

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal,
            new ExerciseRepository([]),
            new KeyLighter(),
            $resultRendererFactory,
        );


        $result = new Failure('Failure', 'Some Failure');
        $this->assertSame("         Some Failure\n", $renderer->renderResult($result));
    }

    public function testLineBreak(): void
    {
        $color = new Color();
        $color->setForceStyle(true);

        $terminal = $this->createMock(Terminal::class);
        $terminal->method('getWidth')->willReturn(10);

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal,
            new ExerciseRepository([]),
            new KeyLighter(),
            new ResultRendererFactory(),
        );

        $this->assertSame("\e[33m──────────\e[0m", $renderer->lineBreak());
    }

    public function testRenderSuccess(): void
    {
        $color = new Color();
        $color->setForceStyle(true);

        $resultRendererFactory = new ResultRendererFactory();

        $terminal = $this->createMock(Terminal::class);
        $terminal->method('getWidth')->willReturn(100);

        $exerciseRepo = $this->createMock(ExerciseRepository::class);
        $exerciseRepo->method('count')->willReturn(2);

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal,
            $exerciseRepo,
            new KeyLighter(),
            $resultRendererFactory,
        );

        $resultSet = new ResultAggregator();
        $resultSet->add(new Success('Success 1!'));
        $resultSet->add(new Success('Success 2!'));

        $this->expectOutputString($this->getExpectedOutput());

        $renderer->render(
            $resultSet,
            $this->createMock(ExerciseInterface::class),
            new UserState(['exercise1']),
            new StdOutput($color, $terminal),
        );
    }

    public function testRenderSuccessWithSolution(): void
    {
        $color = new Color();
        $color->setForceStyle(true);

        $resultRendererFactory = new ResultRendererFactory();

        $terminal = $this->createMock(Terminal::class);
        $terminal->method('getWidth')->willReturn(100);

        $exerciseRepo = $this->createMock(ExerciseRepository::class);
        $exerciseRepo->method('count')->willReturn(2);

        $tmpFile = $this->getTemporaryFile('some-file', 'FILE CONTENTS');

        $exercise = new CliExerciseImpl();
        $exercise->setSolution(new SingleFileSolution($tmpFile));

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal,
            $exerciseRepo,
            new KeyLighter(),
            $resultRendererFactory,
        );

        $resultSet = new ResultAggregator();
        $resultSet->add(new Success('Success 1!'));
        $resultSet->add(new Success('Success 2!'));

        $this->expectOutputString($this->getExpectedOutput());

        $renderer->render(
            $resultSet,
            $exercise,
            new UserState(['exercise1']),
            new StdOutput($color, $terminal),
        );
    }

    public function testRenderSuccessWithPhpSolutionFileIsSyntaxHighlighted(): void
    {
        $color = new Color();
        $color->setForceStyle(true);

        $resultRendererFactory = new ResultRendererFactory();

        $terminal = $this->createMock(Terminal::class);
        $terminal->method('getWidth')->willReturn(100);

        $exerciseRepo = $this->createMock(ExerciseRepository::class);
        $exerciseRepo->method('count')->willReturn(2);

        $tmpFile = $this->getTemporaryFile('some-file.php', 'FILE CONTENTS');

        $exercise = new CliExerciseImpl();
        $exercise->setSolution(new SingleFileSolution($tmpFile));

        $syntaxHighlighter = $this->createMock(KeyLighter::class);
        $php = new Php();
        $syntaxHighlighter->method('languageByExt')->with('.php')->willReturn($php);
        $syntaxHighlighter
            ->method('highlight')
            ->with('FILE CONTENTS', $php, $this->isInstanceOf(CliFormatter::class))
            ->willReturn('FILE CONTENTS');

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal,
            $exerciseRepo,
            $syntaxHighlighter,
            $resultRendererFactory,
        );

        $resultSet = new ResultAggregator();
        $resultSet->add(new Success('Success 1!'));
        $resultSet->add(new Success('Success 2!'));

        $this->expectOutputString($this->getExpectedOutput());

        $renderer->render(
            $resultSet,
            $exercise,
            new UserState(['exercise1']),
            new StdOutput($color, $terminal),
        );
    }

    public function testRenderSuccessAndFailure(): void
    {
        $color = new Color();
        $color->setForceStyle(true);

        $resultRendererFactory = new ResultRendererFactory();
        $resultRendererFactory->registerRenderer(Failure::class, FailureRenderer::class, function (Failure $failure) {
            $renderer = $this->createMock(FailureRenderer::class);
            $renderer
                ->method('render')
                ->with($this->isInstanceOf(ResultsRenderer::class))
                ->willReturn($failure->getReason() . "\n");
            return $renderer;
        });

        $terminal = $this->createMock(Terminal::class);
        $terminal->method('getWidth')->willReturn(100);

        $exerciseRepo = $this->createMock(ExerciseRepository::class);
        $exerciseRepo->method('count')->willReturn(2);

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal,
            $exerciseRepo,
            new KeyLighter(),
            $resultRendererFactory,
        );

        $resultSet = new ResultAggregator();
        $resultSet->add(new Success('Success 1!'));
        $resultSet->add(new Failure('Check 1', 'Failure'));
        $resultSet->add(new Failure('Check 2', 'Failure'));

        $this->expectOutputString($this->getExpectedOutput());

        $renderer->render(
            $resultSet,
            $this->createMock(ExerciseInterface::class),
            new UserState(),
            new StdOutput($color, $terminal),
        );
    }

    public function testAllSuccessResultsAreHoistedToTheTop(): void
    {
        $color = new Color();
        $color->setForceStyle(true);

        $resultRendererFactory = new ResultRendererFactory();
        $resultRendererFactory->registerRenderer(Failure::class, FailureRenderer::class, function (Failure $failure) {
            $renderer = $this->createMock(FailureRenderer::class);
            $renderer
                ->method('render')
                ->with($this->isInstanceOf(ResultsRenderer::class))
                ->willReturn($failure->getReason() . "\n");
            return $renderer;
        });

        $terminal = $this->createMock(Terminal::class);
        $terminal->method('getWidth')->willReturn(100);

        $exerciseRepo = $this->createMock(ExerciseRepository::class);
        $exerciseRepo->method('count')->willReturn(2);

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal,
            $exerciseRepo,
            new KeyLighter(),
            $resultRendererFactory,
        );

        $resultSet = new ResultAggregator();
        $resultSet->add(new Failure('Failure 1', 'Failure 1'));
        $resultSet->add(new Success('Success 1!'));
        $resultSet->add(new Failure('Failure 2', 'Failure 2'));
        $resultSet->add(new Success('Success 2!'));

        $this->expectOutputString($this->getExpectedOutput());

        $renderer->render(
            $resultSet,
            $this->createMock(ExerciseInterface::class),
            new UserState(),
            new StdOutput($color, $terminal),
        );
    }

    public function testRenderAllFailures(): void
    {
        $color = new Color();
        $color->setForceStyle(true);

        $resultRendererFactory = new ResultRendererFactory();
        $resultRendererFactory->registerRenderer(Failure::class, FailureRenderer::class, function (Failure $failure) {
            $renderer = $this->createMock(FailureRenderer::class);
            $renderer
                ->method('render')
                ->with($this->isInstanceOf(ResultsRenderer::class))
                ->willReturn($failure->getReason() . "\n");
            return $renderer;
        });

        $terminal = $this->createMock(Terminal::class);
        $terminal->method('getWidth')->willReturn(100);

        $exerciseRepo = $this->createMock(ExerciseRepository::class);
        $exerciseRepo->method('count')->willReturn(2);
        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal,
            $exerciseRepo,
            new KeyLighter(),
            $resultRendererFactory,
        );

        $resultSet = new ResultAggregator();
        $resultSet->add(new Failure('Failure 1', 'Failure 1'));
        $resultSet->add(new Failure('Failure 2', 'Failure 2'));

        $this->expectOutputString($this->getExpectedOutput());

        $renderer->render(
            $resultSet,
            $this->createMock(ExerciseInterface::class),
            new UserState(),
            new StdOutput($color, $terminal),
        );
    }

    private function getExpectedOutput(): string
    {
        $name = camel_case_to_kebab_case($this->getName());
        return file_get_contents(sprintf('%s/../res/exercise-renderer/%s.txt', __DIR__, $name));
    }
}
