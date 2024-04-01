<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Utils\Path;
use PhpSchool\PhpWorkshopTest\BaseTest;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Check\FileExistsCheck;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;

class FileExistsCheckTest extends BaseTest
{
    private FileExistsCheck $check;

    public function setUp(): void
    {
        $this->check = new FileExistsCheck();
    }

    public function testCheckMeta(): void
    {
        $this->assertEquals('File Exists Check', $this->check->getName());
        $this->assertEquals(ExerciseInterface::class, $this->check->getExerciseInterface());
        $this->assertEquals(SimpleCheckInterface::CHECK_BEFORE, $this->check->getPosition());

        $this->assertTrue($this->check->canRun(ExerciseType::CGI()));
        $this->assertTrue($this->check->canRun(ExerciseType::CLI()));
    }

    public function testSuccess(): void
    {
        $context = TestContext::withEnvironment();

        $this->createFileInEnvironment(
            $context->getExecutionContext()->studentEnvironment,
            'solution.php',
            '<?php echo "Hello World";'
        );

        $this->assertInstanceOf(
            Success::class,
            $this->check->check($context->getExecutionContext())
        );
    }

    public function testFailure(): void
    {
        $context = TestContext::withoutEnvironment();

        $failure = $this->check->check($context->getExecutionContext());
        $this->assertInstanceOf(Failure::class, $failure);
        $this->assertEquals(
            sprintf(
                'File: "%s" does not exist',
                Path::join($context->studentWorkingDirectory, 'solution.php')
            ),
            $failure->getReason()
        );
    }
}
