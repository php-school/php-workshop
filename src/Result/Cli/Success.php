<?php

namespace PhpSchool\PhpWorkshop\Result\Cli;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use Psr\Http\Message\RequestInterface;

/**
 * Default implementation of `PhpSchool\PhpWorkshop\Result\Cli\SuccessInterface`.
 *
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Success implements SuccessInterface
{
    /**
     * @var ArrayObject
     */
    private $args;

    /**
     * @var string
     */
    private $name = 'CLI Program Runner';

    /**
     * @param ArrayObject $args The arguments for this success.
     */
    public function __construct(ArrayObject $args)
    {
        $this->args = $args;
    }

    /**
     * Get the name of the check that this result was produced from.
     *
     * @return string
     */
    public function getCheckName()
    {
        return $this->name;
    }

    /**
     * Get the arguments for this success.
     *
     * @return ArrayObject
     */
    public function getArgs()
    {
        return $this->args;
    }
}
