<?php

namespace PhpSchool\PhpWorkshop\Exception;

use RuntimeException;

/**
 * Represents the situation where a reference solution cannot be executed (this should only really happen during
 * workshop development).
 */
class SolutionExecutionException extends RuntimeException
{

}
