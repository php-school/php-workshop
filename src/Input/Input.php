<?php

namespace PhpSchool\PhpWorkshop\Input;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;

/**
 * Container for command arguments
 */
class Input
{
    /**
     * @var string
     */
    private $appName;

    /**
     * @var array
     */
    private $arguments = [];

    /**
     * Input constructor.
     * @param $appName
     * @param array $arguments
     */
    public function __construct($appName, array $arguments = [])
    {
        $this->appName = $appName;
        $this->arguments = $arguments;
    }

    /**
     * @return string
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasArgument($name)
    {
        return isset($this->arguments[$name]);
    }

    /**
     * @param string $name
     * @return string
     * @throws InvalidArgumentException
     */
    public function getArgument($name)
    {
        if (!$this->hasArgument($name)) {
            throw new InvalidArgumentException(sprintf('Argument with name: "%s" does not exist', $name));
        }

        return $this->arguments[$name];
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setArgument($name, $value)
    {
        $this->arguments[$name] = $value;
    }
}
