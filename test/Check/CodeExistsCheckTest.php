<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use PhpParser\ParserFactory;
use PhpSchool\PhpWorkshop\Check\CodeExistsCheck;
use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Input\Input;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Check\FileExistsCheck;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;

class CodeExistsCheckTest extends TestCase
{
    /**
     * @var string
     */
    private $testDir;

    /**
     * @var FileExistsCheck
     */
    private $check;

    /**
     * @var ExerciseInterface
     */
    private $exercise;

    /**
     * @var string
     */
    private $file;

    public function setUp(): void
    {
        $this->testDir = sprintf(
            '%s/%s/%s',
            str_replace('\\', '/', sys_get_temp_dir()),
            basename(str_replace('\\', '/', get_class($this))),
            $this->getName()
        );

        mkdir($this->testDir, 0777, true);
        $this->check = new CodeExistsCheck((new ParserFactory())->create(ParserFactory::PREFER_PHP7));
        $this->exercise = $this->createMock(ExerciseInterface::class);
        $this->assertEquals('Code Exists Check', $this->check->getName());
        $this->assertEquals(ExerciseInterface::class, $this->check->getExerciseInterface());
        $this->assertEquals(SimpleCheckInterface::CHECK_BEFORE, $this->check->getPosition());

        $this->assertTrue($this->check->canRun(ExerciseType::CGI()));
        $this->assertTrue($this->check->canRun(ExerciseType::CLI()));

        $this->file = sprintf('%s/submission.php', $this->testDir);
        touch($this->file);
    }

    public function testSuccess(): void
    {
        file_put_contents($this->file, '<?php echo "Hello World";');

        $this->assertInstanceOf(
            Success::class,
            $this->check->check($this->exercise, new Input('app', ['program' => $this->file]))
        );
    }

    public function testFailure(): void
    {
        file_put_contents($this->file, '<?php');

        $failure = $this->check->check($this->exercise, new Input('app', ['program' => $this->file]));

        $this->assertInstanceOf(Failure::class, $failure);
        $this->assertEquals('No code was found', $failure->getReason());
    }

    public function tearDown(): void
    {
        unlink($this->file);
        rmdir($this->testDir);
    }
}
