<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use Colors\Color;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Factory\ResultRendererFactory;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\ResultRenderer\FailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultRendererInterface;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultsRenderer;
use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PSX\Factory;
use PhpSchool\PSX\SyntaxHighlighter;
use PHPUnit_Framework_TestCase;
use Prophecy\Argument;

/**
 * Class ResultsRendererTest
 * @package PhpSchool\PhpWorkshopTest\ResultRenderer
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ResultsRendererTest extends PHPUnit_Framework_TestCase
{

    public function testRenderIndividualResult()
    {
        $color = new Color;
        $color->setForceStyle(true);

        $resultRendererFactory = new ResultRendererFactory;
        $resultRendererFactory->registerRenderer(Failure::class, FailureRenderer::class);

        $terminal = $this->prophesize(TerminalInterface::class);
        $terminal->getWidth()->willReturn(30);

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal->reveal(),
            new ExerciseRepository([]),
            (new Factory)->__invoke(),
            $resultRendererFactory
        );


        $result = new Failure('Failure', 'Some Failure');
        $this->assertSame("         Some Failure\n", $renderer->renderResult($result));
    }

    public function testLineBreak()
    {
        $color = new Color;
        $color->setForceStyle(true);

        $terminal = $this->prophesize(TerminalInterface::class);
        $terminal->getWidth()->willReturn(10);

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal->reveal(),
            new ExerciseRepository([]),
            (new Factory)->__invoke(),
            new ResultRendererFactory
        );

        $this->assertSame("\e[33m──────────\e[0m", $renderer->lineBreak());
    }

    public function testRenderSuccess()
    {
        $color = new Color;
        $color->setForceStyle(true);

        $resultRendererFactory = new ResultRendererFactory;

        $terminal = $this->prophesize(TerminalInterface::class);
        $terminal->getWidth()->willReturn(100);
        $terminal = $terminal->reveal();

        $exerciseRepo = $this->prophesize(ExerciseRepository::class);
        $exerciseRepo->count()->willReturn(2);

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal,
            $exerciseRepo->reveal(),
            (new Factory)->__invoke(),
            $resultRendererFactory
        );

        $resultSet = new ResultAggregator;
        $resultSet->add(new Success('Success 1!'));
        $resultSet->add(new Success('Success 2!'));

        $this->expectOutputString($this->getExpectedOutput());

        $renderer->render(
            $resultSet,
            $this->createMock(ExerciseInterface::class),
            new UserState(['exercise1']),
            new StdOutput($color, $terminal)
        );
    }

    public function testRenderSuccessWithSolution()
    {
        $color = new Color;
        $color->setForceStyle(true);

        $resultRendererFactory = new ResultRendererFactory;

        $terminal = $this->prophesize(TerminalInterface::class);
        $terminal->getWidth()->willReturn(100);
        $terminal = $terminal->reveal();

        $exerciseRepo = $this->prophesize(ExerciseRepository::class);
        $exerciseRepo->count()->willReturn(2);

        $tmpFile = sprintf('%s/%s/some-file', sys_get_temp_dir(), $this->getName());
        mkdir(dirname($tmpFile));
        file_put_contents($tmpFile, 'FILE CONTENTS');

        $solution = new SingleFileSolution($tmpFile);

        $exercise = $this->prophesize(ExerciseInterface::class);
        $exercise->willImplement(ProvidesSolution::class);
        $exercise->getSolution()->willReturn($solution);

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal,
            $exerciseRepo->reveal(),
            (new Factory)->__invoke(),
            $resultRendererFactory
        );

        $resultSet = new ResultAggregator;
        $resultSet->add(new Success('Success 1!'));
        $resultSet->add(new Success('Success 2!'));

        $this->expectOutputString($this->getExpectedOutput());

        $renderer->render(
            $resultSet,
            $exercise->reveal(),
            new UserState(['exercise1']),
            new StdOutput($color, $terminal)
        );

        unlink($tmpFile);
        rmdir(dirname($tmpFile));
    }

    public function testRenderSuccessWithPhpSolutionFileIsSyntaxHighlighted()
    {
        $color = new Color;
        $color->setForceStyle(true);

        $resultRendererFactory = new ResultRendererFactory;

        $terminal = $this->prophesize(TerminalInterface::class);
        $terminal->getWidth()->willReturn(100);
        $terminal = $terminal->reveal();

        $exerciseRepo = $this->prophesize(ExerciseRepository::class);
        $exerciseRepo->count()->willReturn(2);

        $tmpFile = sprintf('%s/%s/some-file.php', sys_get_temp_dir(), $this->getName());
        mkdir(dirname($tmpFile));
        file_put_contents($tmpFile, 'FILE CONTENTS');

        $solution = new SingleFileSolution($tmpFile);

        $exercise = $this->prophesize(ExerciseInterface::class);
        $exercise->willImplement(ProvidesSolution::class);
        $exercise->getSolution()->willReturn($solution);

        $syntaxHighlighter = $this->prophesize(SyntaxHighlighter::class);
        $syntaxHighlighter->highlight('FILE CONTENTS')->willReturn('FILE CONTENTS');

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal,
            $exerciseRepo->reveal(),
            $syntaxHighlighter->reveal(),
            $resultRendererFactory
        );

        $resultSet = new ResultAggregator;
        $resultSet->add(new Success('Success 1!'));
        $resultSet->add(new Success('Success 2!'));

        $this->expectOutputString($this->getExpectedOutput());

        $renderer->render(
            $resultSet,
            $exercise->reveal(),
            new UserState(['exercise1']),
            new StdOutput($color, $terminal)
        );

        unlink($tmpFile);
        rmdir(dirname($tmpFile));
    }

    public function testRenderSuccessAndFailure()
    {
        $color = new Color;
        $color->setForceStyle(true);

        $resultRendererFactory = new ResultRendererFactory;
        $resultRendererFactory->registerRenderer(Failure::class, FailureRenderer::class, function (Failure $failure) {
             $renderer = $this->prophesize(FailureRenderer::class);
             $renderer->render(Argument::type(ResultsRenderer::class))->willReturn($failure->getReason() . "\n");
             return $renderer->reveal();
        });

        $terminal = $this->prophesize(TerminalInterface::class);
        $terminal->getWidth()->willReturn(100);
        $terminal = $terminal->reveal();

        $exerciseRepo = $this->prophesize(ExerciseRepository::class);
        $exerciseRepo->count()->willReturn(2);

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal,
            $exerciseRepo->reveal(),
            (new Factory)->__invoke(),
            $resultRendererFactory
        );

        $resultSet = new ResultAggregator;
        $resultSet->add(new Success('Success 1!'));
        $resultSet->add(new Failure('Check 1', 'Failure'));
        $resultSet->add(new Failure('Check 2', 'Failure'));

        $this->expectOutputString($this->getExpectedOutput());

        $renderer->render(
            $resultSet,
            $this->createMock(ExerciseInterface::class),
            new UserState,
            new StdOutput($color, $terminal)
        );
    }

    public function testAllSuccessResultsAreHoistedToTheTop()
    {
        $color = new Color;
        $color->setForceStyle(true);

        $resultRendererFactory = new ResultRendererFactory;
        $resultRendererFactory->registerRenderer(Failure::class, FailureRenderer::class, function (Failure $failure) {
            $renderer = $this->prophesize(FailureRenderer::class);
            $renderer->render(Argument::type(ResultsRenderer::class))->willReturn($failure->getReason() . "\n");
            return $renderer->reveal();
        });

        $terminal = $this->prophesize(TerminalInterface::class);
        $terminal->getWidth()->willReturn(100);
        $terminal = $terminal->reveal();

        $exerciseRepo = $this->prophesize(ExerciseRepository::class);
        $exerciseRepo->count()->willReturn(2);

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal,
            $exerciseRepo->reveal(),
            (new Factory)->__invoke(),
            $resultRendererFactory
        );

        $resultSet = new ResultAggregator;
        $resultSet->add(new Failure('Failure 1', 'Failure 1'));
        $resultSet->add(new Success('Success 1!'));
        $resultSet->add(new Failure('Failure 2', 'Failure 2'));
        $resultSet->add(new Success('Success 2!'));

        $this->expectOutputString($this->getExpectedOutput());

        $renderer->render(
            $resultSet,
            $this->createMock(ExerciseInterface::class),
            new UserState,
            new StdOutput($color, $terminal)
        );
    }

    public function testRenderAllFailures()
    {
        $color = new Color;
        $color->setForceStyle(true);

        $resultRendererFactory = new ResultRendererFactory;
        $resultRendererFactory->registerRenderer(Failure::class, FailureRenderer::class, function (Failure $failure) {
            $renderer = $this->prophesize(FailureRenderer::class);
            $renderer->render(Argument::type(ResultsRenderer::class))->willReturn($failure->getReason() . "\n");
            return $renderer->reveal();
        });

        $terminal = $this->prophesize(TerminalInterface::class);
        $terminal->getWidth()->willReturn(100);
        $terminal = $terminal->reveal();

        $exerciseRepo = $this->prophesize(ExerciseRepository::class);
        $exerciseRepo->count()->willReturn(2);

        $renderer = new ResultsRenderer(
            'app',
            $color,
            $terminal,
            $exerciseRepo->reveal(),
            (new Factory)->__invoke(),
            $resultRendererFactory
        );

        $resultSet = new ResultAggregator;
        $resultSet->add(new Failure('Failure 1', 'Failure 1'));
        $resultSet->add(new Failure('Failure 2', 'Failure 2'));

        $this->expectOutputString($this->getExpectedOutput());

        $renderer->render(
            $resultSet,
            $this->createMock(ExerciseInterface::class),
            new UserState,
            new StdOutput($color, $terminal)
        );
    }

    /**
     * @return string
     */
    private function getExpectedOutput()
    {
        $name = camel_case_to_kebab_case($this->getName());
        return file_get_contents(sprintf('%s/../res/exercise-renderer/%s.txt', __DIR__, $name));
    }
}
