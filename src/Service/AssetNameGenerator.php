<?php

namespace GoldenPlanet\WebpackBundle\Service;

class AssetNameGenerator {

	/**
	 * Appending a hash of the asset's full path to the file name
	 * for entry points with same file names (e.g. multiple main.js)
	 *
	 * Thus there's no need to specify a specific
	 * file name for the output (like in Assetic)
	 *
	 * @param string $asset
	 *
	 * @return string
	 */
	public function generateName($asset) {
		return sprintf('%s.%s', pathinfo($asset, PATHINFO_FILENAME), hash('crc32b', $asset));
	}
}
