<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use PhpParser\ParserFactory;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Check\CodeParseCheck;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PHPUnit_Framework_TestCase;

/**
 * Class CodeParseCheckTest
 * @package PhpSchool\PhpWorkshopTest\Check
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CodeParseCheckTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CheckInterface
     */
    private $check;

    /**
     * @var string
     */
    private $file;
    
    public function setUp()
    {
        $this->check = new CodeParseCheck((new ParserFactory)->create(ParserFactory::PREFER_PHP7));
        $this->assertEquals('Code Parse Check', $this->check->getName());
        $this->assertEquals(ExerciseInterface::class, $this->check->getExerciseInterface());

        $this->assertTrue($this->check->canRun(ExerciseType::CGI()));
        $this->assertTrue($this->check->canRun(ExerciseType::CLI()));

        $this->file = sprintf('%s/%s/submission.php', str_replace('\\', '/', sys_get_temp_dir()), $this->getName());
        mkdir(dirname($this->file), 0775, true);
        touch($this->file);
    }

    public function testUnParseableCodeReturnsFailure()
    {
        file_put_contents($this->file, '<?php $lol');
        
        $result = $this->check->check($this->getMock(ExerciseInterface::class), $this->file);
        $this->assertInstanceOf(Failure::class, $result);
        
        $this->assertEquals('Code Parse Check', $result->getCheckName());
        $this->assertRegExp(
            sprintf('|^File: "%s" could not be parsed\. Error: "|', preg_quote($this->file)),
            $result->getReason()
        );
    }

    public function testParseableCodeReturnsSuccess()
    {
        file_put_contents($this->file, '<?php $lol = "lol";');

        $result = $this->check->check($this->getMock(ExerciseInterface::class), $this->file);
        $this->assertInstanceOf(Success::class, $result);

        $this->assertEquals('Code Parse Check', $result->getCheckName());
    }

    public function tearDown()
    {
        unlink($this->file);
        rmdir(dirname($this->file));
    }
}
