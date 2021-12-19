<?php

namespace PhpSchool\PhpWorkshop\Markdown;

interface Renderer
{
    public function render(string $markdown): string;
}
