<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Utils;

/**
 * ArrayObject is not a good name...
 * @template TKey of array-key
 * @template T
 * @extends ArrayObject<TKey, T>
 */
class Collection extends ArrayObject
{
}
