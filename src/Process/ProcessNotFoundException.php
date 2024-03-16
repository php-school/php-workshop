<?php

namespace PhpSchool\PhpWorkshop\Process;

class ProcessNotFoundException extends \RuntimeException
{
    public static function fromExecutable(string $executable): self
    {
        return new self(sprintf('Could not find executable: "%s"', $executable));
    }
}
