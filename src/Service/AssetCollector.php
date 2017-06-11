<?php

namespace GoldenPlanet\WebpackBundle\Service;

use ArrayObject;
use GoldenPlanet\WebpackBundle\Provider\AssetProvider\AssetsFromFileProviderInterface;
use GoldenPlanet\WebpackBundle\Model\AssetItemModel;
use GoldenPlanet\WebpackBundle\Model\AssetCollectionModel;
use GoldenPlanet\WebpackBundle\ErrorHandler\ErrorHandlerInterface;
use GoldenPlanet\WebpackBundle\Exception\ResourceParsingException;

class AssetCollector
{
	/**
	 * @var AssetsFromFileProviderInterface[]
	 */
	private $assetProviders = [];
	private $errorHandler;

	public function __construct(
		ErrorHandlerInterface $errorHandler
	) {
		$this->errorHandler = $errorHandler;
	}

	/**
	 * This method is called for each service tagged with `gp_webpack.asset_provider`
	 *
	 * A compiler pass is added in the root `GoldenPlanetWebpackBundle->build()` method
	 * for all services that are tagged with `tags: - { name: gp_webpack.asset_provider }`
	 *
	 * @param AssetsFromFileProviderInterface $assetProvider
	 */
	public function addAssetProvider($assetProvider)
	{
		$this->assetProviders[] = $assetProvider;
	}

	/**
	 * @param null|mixed $previousContext
	 *
	 * @return AssetCollectionModel
	 */
	public function getAssets($previousContext = null)
	{
		$contexts = [];
		$groupedAssets = new ArrayObject();

		foreach ($this->assetProviders as $i => $assetProvider) {

			$assetProviderContext = isset($previousContext[$i]) ? $previousContext[$i] : null;
			$assetCollection = $assetProvider->getAssets($assetProviderContext);
			$contexts[$i] = $assetCollection->getContext();
			$assetProviderAssets = $assetCollection->getAssets();

			$this->mergeAssets($groupedAssets, $assetProviderAssets);
		}

		return $this->buildCollection($groupedAssets, $contexts);
	}

	/**
	 * @param ArrayObject      $groupedAssets
	 * @param AssetItemModel[] $assets
	 */
	private function mergeAssets(ArrayObject $groupedAssets, $assets)
	{
		foreach ($assets as $asset) {

			$assetResource = $asset->getResource();

			if (isset($groupedAssets[$assetResource])) {

				$this->checkSameGroup($groupedAssets[$assetResource], $asset);
				continue;
			}

			$groupedAssets[$assetResource] = $asset;
		}
	}

	/**
	 * Ensure that an asset does not belong to multiple groups
	 * It must be a part of a single commons chunk
	 *
	 * @param AssetItemModel $assetOne
	 * @param AssetItemModel $assetTwo
	 */
	private function checkSameGroup(AssetItemModel $assetOne, AssetItemModel $assetTwo)
	{
		if ($assetOne->getGroup() !== $assetTwo->getGroup()) {

			$this->errorHandler
				->processException(new ResourceParsingException(sprintf(
						'Same assets must have same groups. Different groups (%s and %s) found for asset "%s"',
						$assetOne->getGroup() === null ? 'none' : '"' . $assetOne->getGroup() . '"',
						$assetTwo->getGroup() === null ? 'none' : '"' . $assetTwo->getGroup() . '"',
						$assetOne->getResource()
					))
				);
		}
	}

	private function buildCollection(ArrayObject $groupedAssets, $context)
	{
		$assetCollection = new AssetCollectionModel();
		$assetCollection->setAssets(array_values((array) $groupedAssets));
		$assetCollection->setContext($context);

		return $assetCollection;
	}
}
