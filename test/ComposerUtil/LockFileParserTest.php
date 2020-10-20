<?php

namespace PhpSchool\PhpWorkshopTest\ComposerUtil;

use PhpSchool\PhpWorkshop\ComposerUtil\LockFileParser;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Class LockFileParserTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class LockFileParserTest extends TestCase
{
    public function testGetPackages(): void
    {
        $locker = new LockFileParser(__DIR__ . '/../res/composer.lock');
        $this->assertEquals([
            ['name' => 'danielstjules/stringy', 'version' => '2.1.0'],
            ['name' => 'klein/klein', 'version' => 'v2.1.0'],
        ], $locker->getInstalledPackages());
    }

    public function testHasPackage(): void
    {
        $locker = new LockFileParser(__DIR__ . '/../res/composer.lock');
        $this->assertTrue($locker->hasInstalledPackage('danielstjules/stringy'));
        $this->assertTrue($locker->hasInstalledPackage('klein/klein'));
        $this->assertFalse($locker->hasInstalledPackage('not-a-package'));
    }

    public function testExceptionIsThrownIfFileNotExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Lock File: "not-a-file" does not exist');
        new LockFileParser('not-a-file');
    }
}
