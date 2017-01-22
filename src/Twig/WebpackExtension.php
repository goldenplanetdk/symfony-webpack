<?php

namespace GoldenPlanet\WebpackBundle\Twig;

use GoldenPlanet\WebpackBundle\Service\AssetManager;
use Twig_Extension as Extension;
use Twig_SimpleFunction as SimpleFunction;

class WebpackExtension extends Extension {

	const FUNCTION_NAME = 'webpack_asset';
	const TAG_NAME_STYLESHEETS = 'webpack_stylesheets';
	const TAG_NAME_JAVASCRIPTS = 'webpack_javascripts';
	const TAG_NAME_ASSETS = 'webpack_assets';

	protected $assetManager;

	public function __construct(AssetManager $assetManager) {
		$this->assetManager = $assetManager;
	}

	public function getFunctions() {
		return [
			new SimpleFunction(self::FUNCTION_NAME, [$this, 'getAssetUrl']),
		];
	}

	public function getTokenParsers() {
		return [
			new WebpackTokenParser(self::TAG_NAME_STYLESHEETS, self::FUNCTION_NAME, 'css'),
			new WebpackTokenParser(self::TAG_NAME_JAVASCRIPTS, self::FUNCTION_NAME, 'js'),
			new WebpackTokenParser(self::TAG_NAME_ASSETS, self::FUNCTION_NAME, null),
		];
	}

	public function getAssetUrl($resource, $type = null) {
		return $this->assetManager->getAssetUrl($resource, $type);
	}

	public function getName() {
		return 'gp_webpack';
	}
}
