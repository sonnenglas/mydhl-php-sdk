<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . DIRECTORY_SEPARATOR . 'src')
    ->in(__DIR__ . DIRECTORY_SEPARATOR . 'tests');

$rules = [
    '@PSR12' => true,
    '@PSR12:risky' => true,
    '@PHP8x2Migration' => true,
    'declare_strict_types' => true,
    'void_return' => true,
    'no_unused_imports' => true,
    'ordered_imports' => ['sort_algorithm' => 'alpha'],
    'single_quote' => true,
    'array_syntax' => ['syntax' => 'short'],
    'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
    'no_extra_blank_lines' => true,
    'blank_line_before_statement' => ['statements' => ['return']],
    'global_namespace_import' => ['import_classes' => true, 'import_constants' => false, 'import_functions' => false],
];

$config = new PhpCsFixer\Config();

return $config
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setFinder($finder);
