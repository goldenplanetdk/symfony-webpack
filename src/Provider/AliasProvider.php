<?php

namespace GoldenPlanet\WebpackBundle\Provider;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\Container;

class AliasProvider
{
	private $fileLocator;
	private $enabledBundles;
	private $pathInBundle;
	private $additionalAliases;

	/**
	 * @var null|array
	 */
	private $aliases = null;

	/**
	 * @param FileLocatorInterface $fileLocator
	 * @param array                $enabledBundles
	 * @param string               $pathInBundle
	 * @param array                $additionalAliases
	 */
	public function __construct(
		FileLocatorInterface $fileLocator,
		array $enabledBundles,
		$pathInBundle,
		array $additionalAliases
	) {
		$this->fileLocator = $fileLocator;
		$this->enabledBundles = $enabledBundles;
		$this->pathInBundle = $pathInBundle;
		$this->additionalAliases = $additionalAliases;
	}

	/**
	 * Get aliases for enabled bundles and additional aliases (@app, @root and @custom)
	 *
	 * @return array|null
	 */
	public function getAliases()
	{
		// No need to recreate aliases on subsequent calls
		if ($this->aliases !== null) {
			return $this->aliases;
		}

		$aliases = [];

		foreach ($this->enabledBundles as $bundleName) {

			$aliases['@' . $bundleName] = rtrim($this->fileLocator->locate('@' . $bundleName), '/');

			try {
				$shortName = $this->getShortNameForBundle($bundleName);
				$aliases['@' . $shortName] = $this->fileLocator->locate('@' . $bundleName . '/' . $this->pathInBundle);
			} catch (InvalidArgumentException $exception) {
				// ignore if directory not found, as all bundles are enabled by default
			}
		}

		// Give priority to additional aliases to be able to overwrite bundle aliases
		foreach ($this->additionalAliases as $alias => $path) {

			$realPath = realpath($path);

			if ($realPath === false) {
				// Ignore aliases to non-existing directories - no need to have directories for default aliases
				unset($aliases['@' . $alias]);
				continue;
			}

			$aliases['@' . $alias] = $realPath;
		}

		$this->aliases = $aliases;

		return $aliases;
	}

	public function getAliasPath($alias)
	{
		$aliases = $this->getAliases();

		if (!isset($aliases[$alias])) {
			throw new RuntimeException(sprintf('Alias not registered: %s', $alias));
		}

		return $aliases[$alias];
	}

	private function getShortNameForBundle($bundleName)
	{
		$shortName = $bundleName;

		// trim trailing `Bundle`
		if (mb_substr($bundleName, -6) === 'Bundle') {
			$shortName = mb_substr($shortName, 0, -6);
		}

		return lcfirst(Container::camelize($shortName));
	}
}
