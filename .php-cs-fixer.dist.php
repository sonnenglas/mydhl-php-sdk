<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.DIRECTORY_SEPARATOR.'src')
    ->in(__DIR__.DIRECTORY_SEPARATOR.'tests')
    ->append(['.php_cs.dist']);

$rules = [
    '@PSR12' => true,
    '@PHP80Migration' => true,
    'declare_strict_types' => true,
    'void_return' => true,
];

$config = new PhpCsFixer\Config();

return $config
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setFinder($finder);
