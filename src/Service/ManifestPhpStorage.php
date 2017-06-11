<?php

namespace GoldenPlanet\WebpackBundle\Service;

use RuntimeException;

class ManifestPhpStorage
{
	private $manifestPath;

	public function __construct($manifestPath)
	{
		$this->manifestPath = $manifestPath;
	}

	/**
	 * Save `/cache/webpack-manifest.php`
	 *
	 * @param array $manifest
	 */
	public function saveManifest(array $manifest)
	{
		file_put_contents($this->manifestPath, '<?php return ' . var_export($manifest, true) . ';');
	}

	/**
	 * Execute `/cache/webpack-manifest.php`
	 *
	 * @return mixed
	 */
	public function requireManifest()
	{
		if (!file_exists($this->manifestPath)) {

			throw new RuntimeException(sprintf(
				'Manifest file not found: %s. %s',
				$this->manifestPath,
				'You must run webpack:compile or webpack:dev-server before twig can render webpack assets'
			));
		}

		return require $this->manifestPath;
	}
}
