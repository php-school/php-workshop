<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use PhpParser\ParserFactory;
use PhpSchool\PhpWorkshop\Check\CodeParseCheck;
use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\Utils\Path;
use PhpSchool\PhpWorkshopTest\BaseTest;
use PHPUnit\Framework\TestCase;
use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;

class CodeParseCheckTest extends BaseTest
{
    use AssertionRenames;

    private CodeParseCheck $check;

    public function setUp(): void
    {
        $this->check = new CodeParseCheck((new ParserFactory())->create(ParserFactory::PREFER_PHP7));
    }

    public function testCheckMeta(): void
    {
        $this->assertEquals('Code Parse Check', $this->check->getName());
        $this->assertEquals(ExerciseInterface::class, $this->check->getExerciseInterface());
        $this->assertEquals(SimpleCheckInterface::CHECK_BEFORE, $this->check->getPosition());

        $this->assertTrue($this->check->canRun(ExerciseType::CGI()));
        $this->assertTrue($this->check->canRun(ExerciseType::CLI()));
    }

    public function testUnParseableCodeReturnsFailure(): void
    {
        $context = TestContext::withEnvironment();

        $this->createFileInEnvironment(
            $context->studentExecutionDirectory,
            'solution.php',
            '<?php $lol'
        );

        $result = $this->check->check($context);
        $this->assertInstanceOf(Failure::class, $result);

        $this->assertEquals('Code Parse Check', $result->getCheckName());
        $this->assertMatchesRegularExpression(
            sprintf(
                '|^File: "%s" could not be parsed\. Error: "|',
                preg_quote(
                    Path::join($context->studentExecutionDirectory, 'solution.php')
                )
            ),
            $result->getReason()
        );
    }

    public function testParseableCodeReturnsSuccess(): void
    {
        $context = TestContext::withEnvironment();

        $this->createFileInEnvironment(
            $context->studentExecutionDirectory,
            'solution.php',
            '<?php $lol = "lol";'
        );

        $result = $this->check->check($context);
        $this->assertInstanceOf(Success::class, $result);

        $this->assertEquals('Code Parse Check', $result->getCheckName());
    }
}
