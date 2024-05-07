<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use PhpParser\ParserFactory;
use PhpSchool\PhpWorkshop\Check\CodeExistsCheck;
use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshopTest\BaseTest;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Check\FileExistsCheck;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;

class CodeExistsCheckTest extends BaseTest
{
    private CodeExistsCheck $check;

    public function setUp(): void
    {
        $this->check = new CodeExistsCheck((new ParserFactory())->create(ParserFactory::PREFER_PHP7));
    }

    public function testCheckMeta(): void
    {
        $this->assertEquals('Code Exists Check', $this->check->getName());
        $this->assertEquals(ExerciseInterface::class, $this->check->getExerciseInterface());
        $this->assertEquals(SimpleCheckInterface::CHECK_BEFORE, $this->check->getPosition());

        $this->assertTrue($this->check->canRun(ExerciseType::CGI()));
        $this->assertTrue($this->check->canRun(ExerciseType::CLI()));
    }

    public function testSuccess(): void
    {
        $context = TestContext::withDirectories();
        $context->importStudentFileFromString('<?php echo "Hello World";');

        $this->assertInstanceOf(
            Success::class,
            $this->check->check($context)
        );
    }

    public function testFailure(): void
    {
        $context = TestContext::withDirectories();
        $context->importStudentFileFromString('<?php');

        $failure = $this->check->check($context);

        $this->assertInstanceOf(Failure::class, $failure);
        $this->assertEquals('No code was found', $failure->getReason());
    }
}
