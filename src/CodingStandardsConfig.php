<?php

namespace MortenScheel\LaravelStartup;

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

class CodingStandardsConfig extends Config
{
    protected $defaultRules = [
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

    /**
     * The PHP version of the application.
     *
     * @var string
     */
    protected $target;

    /**
     * An array of what version is required
     * for a each syntax fixer.
     *
     * @var array
     */
    protected $fixerPerVersion = [
        'short_array' => '5.4',
        'class_keyword' => '5.5',
        'exponentiation' => '5.6',
        'dirname_level' => '7.0',
        'explicit_indirect_variable' => '7.0',
        'null_coalescing' => '7.0',
        'type_annotations' => '7.0',
        'short_list' => '7.1',
        'void_return' => '7.1',
        'heredoc_indentation' => '7.3',
    ];

    /**
     * Create a new MWL configuration.
     *
     * Commented out lines are deprecated rules,
     * they're kept here for easier updates to the config
     * when PCF updates.
     *
     * @param string|null $target
     */
    public function __construct($target = null)
    {
        parent::__construct('laravel-tools');

        $this->target = $target ?: \PHP_VERSION;

        $this->setRiskyAllowed(true)
            ->setRules($this->defaultRules);
    }

    /**
     * @param string|string[] $folders
     * @param string|null $target
     *
     * @return $this
     */
    public static function forLaravel($folders = [], $target = null)
    {
        $folders = (array)$folders;
        $folders = \array_merge(['app', 'config', 'database', 'routes', 'tests'], $folders);

        return static::fromFolders($folders, $target);
    }

    /**
     * @param string|string[] $folders
     * @param string|null $target
     *
     * @return $this
     */
    public static function fromFolders($folders, $target = null)
    {
        $config = new static($target);

        return $config->setFinder(
            Finder::create()->in($folders)
        );
    }

    /**
     * @return $this
     */
    public function enablePhpunitRules()
    {
        return $this->mergeRules([
            'php_unit_dedicate_assert' => true,
            'php_unit_dedicate_assert_internal_type' => true,
            'php_unit_expectation' => true,
            'php_unit_internal_class' => true,
            'php_unit_mock' => true,
            'php_unit_namespaced' => true,
            'php_unit_no_expectation_annotation' => true,
        ]);
    }

    /**
     * Merge a set of rules with the core ones.
     *
     * @return $this
     */
    public function mergeRules(array $rules)
    {
        return $this->setRules(\array_merge(
            $this->getRules(),
            $rules
        ));
    }

    ////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////// HELPERS ////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * @param string $fixer
     *
     * @return array
     */
    protected function getSupportedSyntax($fixer)
    {
        $syntax = $this->supports($fixer) ? 'short' : 'long';

        return \compact('syntax');
    }

    /**
     * @param string $fixer
     *
     * @return bool
     */
    protected function supports($fixer)
    {
        if (!isset($this->fixerPerVersion[$fixer])) {
            return true;
        }

        return \version_compare($this->target, $this->fixerPerVersion[$fixer]) !== -1;
    }
}
