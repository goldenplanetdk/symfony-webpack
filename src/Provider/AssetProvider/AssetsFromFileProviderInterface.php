<?php

namespace GoldenPlanet\WebpackBundle\Provider\AssetProvider;

use GoldenPlanet\WebpackBundle\Model\AssetCollectionModel;
use GoldenPlanet\WebpackBundle\Exception\InvalidContextException;
use GoldenPlanet\WebpackBundle\Exception\InvalidResourceException;

/**
 * @api
 */
interface AssetsFromFileProviderInterface
{
	/**
	 * @param string     $filePath
	 * @param mixed|null $previousContext Former assets collection data
	 *
	 * @return AssetCollectionModel
	 *
	 * @throws InvalidResourceException
	 * @throws InvalidContextException
	 */
	public function getAssets($filePath, $previousContext = null);
}
