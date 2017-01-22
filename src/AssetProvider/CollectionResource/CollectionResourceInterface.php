<?php

namespace GoldenPlanet\WebpackBundle\AssetProvider\CollectionResource;

use GoldenPlanet\WebpackBundle\Exception\InvalidResourceException;

/**
 * @api
 */
interface CollectionResourceInterface {

	/**
	 * @param mixed $resource
	 *
	 * @return array of mixed
	 *
	 * @throws InvalidResourceException
	 */
	public function getInternalResources($resource);
}
