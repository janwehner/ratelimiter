<?php

namespace PhpCsFixer;

return (new Config())
    ->setUsingCache(true)
    ->setCacheFile(__DIR__.'/.php_cs.cache')
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        'multiline_whitespace_before_semicolons' => true,
        'not_operator_with_space' => true,
        'ordered_imports' => true,
        'phpdoc_order' => true,
        'psr_autoloading' => false,
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'binary_operator_spaces' => [
            'operators' => ['=>' => 'align']
        ],
    ])
    ->setFinder(
        Finder::create()
            ->in(__DIR__.'/src')
            ->in(__DIR__.'/tests')
    );
