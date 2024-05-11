<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('test/res')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS2.0' => true,
        'no_unused_imports' => true,
    ])
    ->setFinder($finder);