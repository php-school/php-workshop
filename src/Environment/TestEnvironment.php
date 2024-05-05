<?php

namespace PhpSchool\PhpWorkshop\Environment;

use PhpSchool\PhpWorkshop\Utils\Collection;

class TestEnvironment
{
    /**
     * @var array<string, string>
     */
    public array $files = [];

    public function withFile(string $relativeFileName, string $content): static
    {
        $this->files[$relativeFileName] = $content;

        return $this;
    }
}
