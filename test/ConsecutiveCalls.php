<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest;

use Closure;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;

/**
 * Replacement for the PHPUnit mock `withConsecutive` API which was removed in PHPUnit 10.
 */
trait ConsecutiveCalls
{
    /**
     * @param list<list<mixed>> $parameterSets Expected arguments (values or constraints) for each consecutive call.
     * @param (Closure(int, mixed...): mixed)|null $return Produces the return value, receives the call index and arguments.
     */
    private function consecutive(array $parameterSets, ?Closure $return = null): Closure
    {
        $call = 0;

        return static function (mixed ...$arguments) use (&$call, $parameterSets, $return): mixed {
            $index = $call++;

            if (!array_key_exists($index, $parameterSets)) {
                Assert::fail(sprintf('Unexpected call #%d, only %d calls expected', $index + 1, count($parameterSets)));
            }

            foreach ($parameterSets[$index] as $position => $expected) {
                $constraint = $expected instanceof Constraint ? $expected : new IsEqual($expected);
                Assert::assertThat(
                    $arguments[$position] ?? null,
                    $constraint,
                    sprintf('Parameter %d of call #%d', $position, $index + 1),
                );
            }

            return $return !== null ? $return($index, ...$arguments) : null;
        };
    }
}
