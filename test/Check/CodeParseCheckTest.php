<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use PhpParser\ParserFactory;
use PhpSchool\PhpWorkshop\Check\CodeParseCheck;
use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PHPUnit\Framework\TestCase;

class CodeParseCheckTest extends TestCase
{
    /**
     * @var SimpleCheckInterface
     */
    private $check;

    /**
     * @var string
     */
    private $file;
    
    public function setUp() : void
    {
        $this->check = new CodeParseCheck((new ParserFactory)->create(ParserFactory::PREFER_PHP7));
        $this->assertEquals('Code Parse Check', $this->check->getName());
        $this->assertEquals(ExerciseInterface::class, $this->check->getExerciseInterface());
        $this->assertEquals(SimpleCheckInterface::CHECK_BEFORE, $this->check->getPosition());

        $this->assertTrue($this->check->canRun(ExerciseType::CGI()));
        $this->assertTrue($this->check->canRun(ExerciseType::CLI()));

        $this->file = sprintf('%s/%s/submission.php', str_replace('\\', '/', sys_get_temp_dir()), $this->getName());
        mkdir(dirname($this->file), 0775, true);
        touch($this->file);
    }

    public function testUnParseableCodeReturnsFailure() : void
    {
        file_put_contents($this->file, '<?php $lol');
        
        $result = $this->check->check(
            $this->createMock(ExerciseInterface::class),
            new Input('app', ['program' => $this->file])
        );
        $this->assertInstanceOf(Failure::class, $result);
        
        $this->assertEquals('Code Parse Check', $result->getCheckName());
        $this->assertMatchesRegularExpression(
            sprintf('|^File: "%s" could not be parsed\. Error: "|', preg_quote($this->file)),
            $result->getReason()
        );
    }

    public function testParseableCodeReturnsSuccess() : void
    {
        file_put_contents($this->file, '<?php $lol = "lol";');

        $result = $this->check->check(
            $this->createMock(ExerciseInterface::class),
            new Input('app', ['program' => $this->file])
        );
        $this->assertInstanceOf(Success::class, $result);

        $this->assertEquals('Code Parse Check', $result->getCheckName());
    }

    public function tearDown(): void
    {
        unlink($this->file);
        rmdir(dirname($this->file));
    }
}
