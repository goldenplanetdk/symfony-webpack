<?php

$finder = PhpCsFixer\Finder::create()
	->in(__DIR__ . '/src')
	->in(__DIR__ . '/tests/functional')
	->in(__DIR__ . '/tests/unit')
;

/**
 * Rules for PHP Coding Standards Fixer
 * http://cs.sensiolabs.org/#using-php-cs-fixer-on-ci
 */
return PhpCsFixer\Config::create()
	->setRules([
		'@Symfony' => true,
		'@Symfony:risky' => true,

		// override Symfony rules
		'concat_space' => [
			'spacing' => 'one',
		],
		'is_null' => [
			'use_yoda_style' => false,
		],

		// additional rules
		'heredoc_to_nowdoc' => true,
		'linebreak_after_opening_tag' => true,
		'no_unreachable_default_argument_value' => true,
		'no_useless_return' => true,
		'phpdoc_add_missing_param_annotation' => true,
		'strict_comparison' => true,
	])
	->setIndent("\t")
	->setLineEnding("\n")
	->setRiskyAllowed(true)
	->setFinder($finder)
	;
