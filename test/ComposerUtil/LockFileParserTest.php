<?php

namespace PhpSchool\PhpWorkshopTest\ComposerUtil;

use PhpSchool\PhpWorkshop\ComposerUtil\LockFileParser;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase;

/**
 * Class LockFileParserTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class LockFileParserTest extends PHPUnit_Framework_TestCase
{
    public function testGetPackages()
    {
        $locker = new LockFileParser(__DIR__ . '/../res/composer.lock');
        $this->assertEquals([
            ['name' => 'danielstjules/stringy', 'version' => '2.1.0'],
            ['name' => 'klein/klein', 'version' => 'v2.1.0'],
        ], $locker->getInstalledPackages());
    }

    public function testHasPackage()
    {
        $locker = new LockFileParser(__DIR__ . '/../res/composer.lock');
        $this->assertTrue($locker->hasInstalledPackage('danielstjules/stringy'));
        $this->assertTrue($locker->hasInstalledPackage('klein/klein'));
        $this->assertFalse($locker->hasInstalledPackage('not-a-package'));
    }

    public function testExceptionIsThrownIfFileNotExists()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Lock File: "not-a-file" does not exist');
        new LockFileParser('not-a-file');
    }
}
