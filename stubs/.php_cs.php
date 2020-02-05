<?php /** @noinspection DuplicatedCode */

use PhpCsFixer\Finder;

require 'vendor/autoload.php';

/*
 * This file will be overwritten if the installation command
 * is run again later. Avoid editing this file, and put
 * custom rule overrides in /project/path/rules.php
 */

$rules = array_merge(
    getDefaultRules(),
    file_exists('rules.php') ? require 'rules.php' : []
);

$finder = Finder::create()->in([
    'app',
    'config',
    'database',
    'routes',
    'tests'
]);

return (new \PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules($rules)
    ->setCacheFile(storage_path('.php_cs.cache'))
    ->setFinder($finder);

function getDefaultRules()
{
    return [
        'psr0' => false,
        '@PSR2' => true,
        'blank_line_after_namespace' => true,
        'braces' => true,
        'class_definition' => true,
        'elseif' => true,
        'function_declaration' => true,
        'indentation_type' => true,
        'line_ending' => true,
        'lowercase_constants' => true,
        'lowercase_keywords' => true,
        'method_argument_space' => [
            'ensure_fully_multiline' => true,
        ],
        'no_break_comment' => true,
        'no_closing_tag' => true,
        'no_spaces_after_function_name' => true,
        'no_spaces_inside_parenthesis' => true,
        'no_trailing_whitespace' => true,
        'no_trailing_whitespace_in_comment' => true,
        'single_blank_line_at_eof' => true,
        'single_class_element_per_statement' => [
            'elements' => ['property'],
        ],
        'single_import_per_statement' => true,
        'single_line_after_imports' => true,
        'switch_case_semicolon_to_colon' => true,
        'switch_case_space' => true,
        'visibility_required' => true,
        'encoding' => true,
        'full_opening_tag' => true,
        'method_chaining_indentation' => true,
        'phpdoc_indent' => true,
        'array_indentation' => true,
        'array_syntax' => [
            'syntax' => 'short'
        ],
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'combine_nested_dirname' => true,
        'concat_space' => [
            'spacing' => 'one'
        ],
        'fully_qualified_strict_types' => true,
        'increment_style' => true,
        'is_null' => true,
        'linebreak_after_opening_tag' => true,
        'mb_str_functions' => true,
        'modernize_types_casting' => true,
        'native_constant_invocation' => true,
        'native_function_invocation' => true,
        'no_extra_blank_lines' => true,
        'no_unused_imports' => true,
        'standardize_increment' => true,
    ];
}
