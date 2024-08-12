<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'single_quote' => true,
        'single_line_comment_style' => ['comment_types' => ['hash']],
        'return_type_declaration' => true,
    ])
    ->setRiskyAllowed(true)
    ->setIndent('    ')
    ->setLineEnding("\n")
    ->setFinder($finder)
;
