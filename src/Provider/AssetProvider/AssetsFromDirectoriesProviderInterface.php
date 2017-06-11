<?php

namespace GoldenPlanet\WebpackBundle\Provider\AssetProvider;

use GoldenPlanet\WebpackBundle\Model\AssetCollectionModel;
use GoldenPlanet\WebpackBundle\Exception\InvalidContextException;
use GoldenPlanet\WebpackBundle\Exception\InvalidResourceException;

/**
 * @api
 */
interface AssetsFromDirectoriesProviderInterface
{
	/**
	 * @param array|null $previousContext Former assets collection data
	 *                                    Each item in the array is a file with it's own collection data
	 *
	 * @return AssetCollectionModel
	 *
	 * @throws InvalidResourceException
	 * @throws InvalidContextException
	 */
	public function getAssets($previousContext = null);
}
