<?php

namespace GoldenPlanet\WebpackBundle\Service;

use GoldenPlanet\WebpackBundle\AssetProvider\AssetProviderInterface;
use GoldenPlanet\WebpackBundle\AssetProvider\AssetResult;

class AssetCollector {

	private $assetProvider;
	private $config;

	/**
	 * AssetCollector constructor.
	 *
	 * @param AssetProviderInterface $assetProvider
	 * @param array                  $config
	 */
	public function __construct(
		AssetProviderInterface $assetProvider,
		array $config
	) {
		$this->assetProvider = $assetProvider;
		$this->config = $config;
	}

	/**
	 * @param null|mixed $previousContext
	 *
	 * @return AssetResult
	 */
	public function getAssets($previousContext = null) {
		return $this->assetProvider->getAssets($this->config, $previousContext);
	}
}
