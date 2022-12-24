<?php

namespace PhpSchool\PhpWorkshopTest\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Result\ComposerFailure;
use PHPUnit\Framework\TestCase;

class ComposerFailureTest extends TestCase
{
    public function testExceptionIsThrownWithInvalidComponent(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $message  = 'Parameter: "missingComponent" can only be one of: ';
        $message .= '"composer.json", "composer.lock", "vendor" ';
        $message .= 'Received: "not-valid-component"';
        $this->expectExceptionMessage($message);

        $check = $this->createMock(CheckInterface::class);
        new ComposerFailure($check, 'not-valid-component');
    }

    public function testWithMissingComponent(): void
    {
        $check = $this->createMock(CheckInterface::class);
        $check
            ->method('getName')
            ->willReturn('Some Check');

        $failure = new ComposerFailure($check, 'composer.json');
        $this->assertSame('Some Check', $failure->getCheckName());
        $this->assertTrue($failure->isMissingComponent());
        $this->assertSame('composer.json', $failure->getMissingComponent());

        $failure = ComposerFailure::fromCheckAndMissingFileOrFolder($check, 'composer.json');
        $this->assertSame('Some Check', $failure->getCheckName());
        $this->assertTrue($failure->isMissingComponent());
        $this->assertSame('composer.json', $failure->getMissingComponent());

        $this->assertEquals(
            [
                'success' => false,
                'name' => 'Some Check',
                'type' => ComposerFailure::class,
                'is_missing_component' => true,
                'is_missing_packages' => false,
                'missing_component' => 'composer.json',
                'missing_packages' => []
            ],
            $failure->toArray()
        );
    }

    public function testWithMissingPackages(): void
    {
        $check = $this->createMock(CheckInterface::class);
        $check
            ->method('getName')
            ->willReturn('Some Check');

        $failure = new ComposerFailure($check, null, ['some/package']);
        $this->assertSame('Some Check', $failure->getCheckName());
        $this->assertTrue($failure->isMissingPackages());
        $this->assertSame(['some/package'], $failure->getMissingPackages());

        $failure = ComposerFailure::fromCheckAndMissingPackages($check, ['some/package']);
        $this->assertSame('Some Check', $failure->getCheckName());
        $this->assertTrue($failure->isMissingPackages());
        $this->assertSame(['some/package'], $failure->getMissingPackages());

        $this->assertEquals(
            [
                'success' => false,
                'name' => 'Some Check',
                'type' => ComposerFailure::class,
                'is_missing_component' => false,
                'is_missing_packages' => true,
                'missing_component' => null,
                'missing_packages' => ['some/package']
            ],
            $failure->toArray()
        );
    }
}
