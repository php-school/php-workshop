<?php

namespace PhpSchool\PhpWorkshopTest\Exercise\Scenario;

use PhpSchool\PhpWorkshop\Exercise\Scenario\CgiScenario;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class CgiScenarioTest extends TestCase
{
    public function testScenario(): void
    {
        $requestOne = $this->createMock(RequestInterface::class);
        $requestTwo = $this->createMock(RequestInterface::class);

        $scenario = (new CgiScenario())
            ->withFile('file1.txt', 'content1')
            ->withFile('file2.txt', 'content2')
            ->withExecution($requestOne)
            ->withExecution($requestTwo);

        static::assertEquals(
            [
                'file1.txt' => 'content1',
                'file2.txt' => 'content2',
            ],
            $scenario->getFiles(),
        );

        static::assertEquals(
            [
                $requestOne,
                $requestTwo,
            ],
            $scenario->getExecutions(),
        );
    }
}
