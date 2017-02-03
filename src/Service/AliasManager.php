<?php

namespace GoldenPlanet\WebpackBundle\Service;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\Container;

class AliasManager {

	private $fileLocator;
	private $registerBundles;
	private $pathInBundle;
	private $additionalAliases;

	/**
	 * @var null|array
	 */
	private $aliases = null;

	/**
	 * @param FileLocatorInterface $fileLocator
	 * @param array                $registerBundles
	 * @param string               $pathInBundle
	 * @param array                $additionalAliases
	 */
	public function __construct(
		FileLocatorInterface $fileLocator,
		array $registerBundles,
		$pathInBundle,
		array $additionalAliases
	) {
		$this->fileLocator = $fileLocator;
		$this->registerBundles = $registerBundles;
		$this->pathInBundle = $pathInBundle;
		$this->additionalAliases = $additionalAliases;
	}

	public function getAliases() {
		if ($this->aliases !== null) {
			return $this->aliases;
		}

		$aliases = [];
		foreach ($this->registerBundles as $bundleName) {
			$aliases['@' . $bundleName] = rtrim($this->fileLocator->locate('@' . $bundleName), '/');
			try {
				$shortName = $this->getShortNameForBundle($bundleName);
				$aliases['@' . $shortName] = $this->fileLocator->locate('@' . $bundleName . '/' . $this->pathInBundle);
			} catch (InvalidArgumentException $exception) {
				// ignore if directory not found, as all bundles are registered by default
			}
		}

		// give priority to additional to be able to overwrite bundle aliases
		foreach ($this->additionalAliases as $alias => $path) {
			$realPath = realpath($path);
			if ($realPath === false) {
				throw new RuntimeException(sprintf('Alias (%s) path not found: %s', $alias, $path));
			}
			$aliases['@' . $alias] = $realPath;
		}

		$this->aliases = $aliases;

		return $aliases;
	}

	public function getAliasPath($alias) {
		$aliases = $this->getAliases();
		if (!isset($aliases[$alias])) {
			throw new RuntimeException(sprintf('Alias not registered: %s', $alias));
		}

		return $aliases[$alias];
	}

	private function getShortNameForBundle($bundleName) {

		$shortName = $bundleName;

		// trim trailing `Bundle`
		if (mb_substr($bundleName, -6) === 'Bundle') {
			$shortName = mb_substr($shortName, 0, -6);
		}

		return lcfirst(Container::camelize($shortName));
	}
}
