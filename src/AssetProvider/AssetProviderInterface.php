<?php

namespace GoldenPlanet\WebpackBundle\AssetProvider;

use GoldenPlanet\WebpackBundle\Exception\InvalidContextException;
use GoldenPlanet\WebpackBundle\Exception\InvalidResourceException;

/**
 * @api
 */
interface AssetProviderInterface {

	/**
	 * @param mixed      $resource value must be json_encode'able (scalar|array)
	 * @param mixed|null $previousContext
	 *
	 * @return AssetResult
	 *
	 * @throws InvalidResourceException
	 * @throws InvalidContextException
	 */
	public function getAssets($resource, $previousContext = null);
}
