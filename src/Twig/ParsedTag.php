<?php

namespace GoldenPlanet\WebpackBundle\Twig;

use Twig_TokenStream;
use Twig_Error_Syntax;

class ParsedTag
{
	private $stream;

	private $inputs = [];
	private $group = null;
	private $type = null;
	private $named = false;

	public function __construct(Twig_TokenStream $stream)
	{
		$this->stream = $stream;
	}

	/**
	 * Input file path to an entry point or to any file
	 * that should be processed with webpack
	 *
	 * @param string $input
	 */
	public function addInput($input)
	{
		$this->inputs[] = $input;
	}

	/**
	 * Commons chunk
	 *
	 * @param string $group
	 */
	public function setGroup($group)
	{
		if ($this->group !== null) {

			$this->throwException(sprintf(
				'Assets can have only a single group, which was already defined for this tag ("%s")',
				$this->group
			));
		}

		$this->group = $group;
		$this->checkNamedWithGroup();
	}

	/**
	 * Type of the asset
	 *
	 * @param string $type Either js or css
	 */
	public function setType($type)
	{
		if ($this->type !== null) {

			$this->throwException(sprintf(
				'Type can be provided only once, type ("%s") was already defined for this tag',
				$this->type
			));
		}

		$this->type = $type;
	}

	/**
	 * Mark commons chunk as named
	 *
	 * Files for that chunk are specified in a webpack config
	 * under `entry[commonsChunkName]`
	 *
	 * e.g. webpack.config.js: `entry.vendor = ['jquery', 'lodash']`
	 * 		index.html.twig  : {% webpack named js 'vendor' %}
	 * 		                or {{ webpack_named_asset('vendor') }}
	 */
	public function markAsNamed()
	{
		$this->named = true;
		$this->checkNamedWithGroup();
	}

	/**
	 * @return array of strings
	 */
	public function getInputs()
	{
		return $this->inputs;
	}

	/**
	 * @return string|null
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * @return string|null
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return bool
	 */
	public function isNamed()
	{
		return $this->named;
	}

	private function throwException($description)
	{
		$token = $this->stream->getCurrent();
		/* @noinspection PhpInternalEntityUsedInspection */
		throw new Twig_Error_Syntax($description, $token->getLine(), $this->stream->getSourceContext());
	}

	private function checkNamedWithGroup()
	{
		if ($this->named && $this->group !== null) {

			$this->throwException(sprintf(
				'Named assets cannot have group assigned, group "%s" was assigned for named asset in this tag',
				$this->group
			));
		}
	}
}
