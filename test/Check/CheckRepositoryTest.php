<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Check\CheckRepository;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Class CheckRepositoryTest
 * @package PhpSchool\PhpWorkshopTest\Check
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CheckRepositoryTest extends TestCase
{
    public function testRegisterViaConstructor() : void
    {
        $check = $this->createMock(CheckInterface::class);
        $repository = new CheckRepository([$check]);
        $this->assertEquals([$check], $repository->getAll());
    }

    public function testRegisterCheck() : void
    {
        $repository = new CheckRepository;
        $this->assertEquals([], $repository->getAll());

        $check = $this->createMock(CheckInterface::class);
        $repository->registerCheck($check);
        $this->assertEquals([$check], $repository->getAll());
    }

    public function testHas() : void
    {
        $repository = new CheckRepository;
        $repository->registerCheck($this->createMock(CheckInterface::class));

        $check = $this->createMock(CheckInterface::class);
        $repository->registerCheck($check);

        $this->assertTrue($repository->has(get_class($check)));
        $this->assertFalse($repository->has('SomeClassWhichDoesNotExist'));
    }

    public function testGetByClassThrowsExceptionIfNotExist() : void
    {
        $repository = new CheckRepository;
        $repository->registerCheck($this->createMock(CheckInterface::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Check: "SomeClassWhichDoesNotExist" does not exist');

        $repository->getByClass('SomeClassWhichDoesNotExist');
    }

    public function testGetByClass() : void
    {
        $repository = new CheckRepository;
        $repository->registerCheck($this->createMock(CheckInterface::class));

        $check = $this->createMock(CheckInterface::class);
        $repository->registerCheck($check);

        $this->assertSame($check, $repository->getByClass(get_class($check)));
    }
}
