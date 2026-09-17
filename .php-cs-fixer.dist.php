<?php

declare(strict_types=1);
use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = new Finder()
    ->in(__DIR__)
    ->exclude(['*/vendor', '*/var', '*/cache'])
;

return new Config()
    ->setRules([
        '@PSR12' => true,
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => null],
        'fully_qualified_strict_types' => ['import_symbols' => true],
        'global_namespace_import' => ['import_classes' => false, 'import_constants' => false, 'import_functions' => false],
        'no_unused_imports' => true,
        'phpdoc_var_annotation_correct_order' => true,
    ])
    ->setFinder($finder)
;
