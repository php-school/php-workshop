<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Util;

use PhpSchool\PhpWorkshop\Utils\StringUtils;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Exception\RuntimeException;

class StringUtilsTest extends TestCase
{
    public function testCanonicalisePath(): void
    {
        $this->assertEquals('/path/to/file', StringUtils::canonicalisePath('/path/to/file'));
        $this->assertEquals('/path/to/file', StringUtils::canonicalisePath('/path/././to/file'));
        $this->assertEquals('/path/to/file', StringUtils::canonicalisePath('/path///to/file'));
        $this->assertEquals('/path/to/file', StringUtils::canonicalisePath('/path/to/file'));
        $this->assertEquals('/', StringUtils::canonicalisePath('/path/to/../../'));
        $this->assertEquals('/path', StringUtils::canonicalisePath('/path/to/some/../../'));
        $this->assertEquals('/some', StringUtils::canonicalisePath('/path/../some/'));
        $this->assertEquals('some', StringUtils::canonicalisePath('path/../some/'));
    }

    public function testExceptionIsThrownIfTryingToTraverseUpPastRoom(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Climbing above the root is not permitted.');

        StringUtils::canonicalisePath('/path/to/../../../');
    }
}
