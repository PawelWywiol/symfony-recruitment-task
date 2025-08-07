<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('docker')
    ->exclude('var')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
    ->ignoreVCSIgnored(true);

return (new PhpCsFixer\Config())
    ->registerCustomFixers(new PhpCsFixerCustomFixers\Fixers())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'class_attributes_separation' => true,
        'declare_strict_types' => true,
        'logical_operators' => true,
        'native_function_invocation' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'allow_unused_params' => false,
        ],
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_summary' => false,
        'php_unit_method_casing' => false,
        'single_line_throw' => false,
        'trailing_comma_in_multiline' => [
            'elements' => [
                'arguments',
                'arrays',
                'match',
                'parameters',
            ],
        ],
        PhpCsFixerCustomFixers\Fixer\PhpdocSingleLineVarFixer::name() => true,
    ])
    ->setFinder($finder)
;
