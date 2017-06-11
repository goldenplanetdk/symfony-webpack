<?php

namespace GoldenPlanet\WebpackBundle\Twig;

use GoldenPlanet\WebpackBundle\Service\AssetManager;
use Twig_Extension;
use Twig_SimpleFunction;

class WebpackExtension extends Twig_Extension
{
	const FUNCTION_NAME = 'webpack_asset';
	const NAMED_ASSET_FUNCTION_NAME = 'webpack_named_asset';

	protected $assetManager;

	public function __construct(AssetManager $assetManager)
	{
		$this->assetManager = $assetManager;
	}

	public function getFunctions()
	{
		return [
			new Twig_SimpleFunction(self::FUNCTION_NAME, [
				$this,
				'getAssetUrl',
			]),
			new Twig_SimpleFunction(self::NAMED_ASSET_FUNCTION_NAME, [
				$this,
				'getNamedAssetUrl',
			]),
		];
	}

	public function getTokenParsers()
	{
		return [
			new WebpackTokenParser(self::FUNCTION_NAME, self::NAMED_ASSET_FUNCTION_NAME),
		];
	}

	/**
	 * @param string      $resource Path to resource. Can begin with alias and be prefixed with loaders
	 * @param string|null $type     Type of asset. If null, type is guessed by extension
	 * @param string|null $group    Not used here - only used when parsing twig templates to group assets
	 *
	 * @return null|string
	 */
	public function getAssetUrl($resource, $type = null, $group = null)
	{
		return $this->assetManager->getAssetUrl($resource, $type);
	}

	/**
	 * @param string $name
	 * @param string $type
	 *
	 * @return null|string
	 */
	public function getNamedAssetUrl($name, $type = null)
	{
		return $this->assetManager->getNamedAssetUrl($name, $type);
	}
}
