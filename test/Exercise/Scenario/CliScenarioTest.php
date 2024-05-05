<?php

namespace PhpSchool\PhpWorkshopTest\Exercise\Scenario;

use PhpSchool\PhpWorkshop\Exercise\Scenario\CliScenario;
use PhpSchool\PhpWorkshop\Utils\Collection;
use PHPUnit\Framework\TestCase;

class CliScenarioTest extends TestCase
{
    public function testScenario(): void
    {
        $scenario = (new CliScenario())
            ->withFile('file1.txt', 'content1')
            ->withFile('file2.txt', 'content2')
            ->withExecution(['arg1', 'arg2'])
            ->withExecution(['arg3', 'arg4']);

        static::assertEquals(
            [
                'file1.txt' => 'content1',
                'file2.txt' => 'content2',
            ],
            $scenario->getFiles()
        );

        static::assertEquals(
            [
                ['arg1', 'arg2'],
                ['arg3', 'arg4'],
            ],
            array_map(
                fn (Collection $collection) => $collection->getArrayCopy(),
                $scenario->getExecutions()
            )
        );
    }
}
