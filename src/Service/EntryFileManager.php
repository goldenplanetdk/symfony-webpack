<?php

namespace GoldenPlanet\WebpackBundle\Service;

class EntryFileManager
{
	private $enabledExtensions;
	private $disabledExtensions;
	private $typeMap;

	/**
	 * @param array $enabledExtensions
	 * @param array $disabledExtensions
	 * @param array $typeMap
	 */
	public function __construct(
		array $enabledExtensions,
		array $disabledExtensions,
		array $typeMap
	) {
		$this->enabledExtensions = $enabledExtensions;
		$this->disabledExtensions = $disabledExtensions;
		$this->typeMap = $typeMap;
	}

	/**
	 * @param string $asset
	 *
	 * @return int|null|string
	 */
	public function getEntryFileType($asset)
	{
		$assetPath = $this->removeLoaders($asset);
		$assetType = strtolower(pathinfo($assetPath, PATHINFO_EXTENSION));

		if ($this->isExtensionIncluded($assetType)) {
			return $this->mapExtension($assetType);
		}

		return null;
	}

	/**
	 * @param string $asset
	 *
	 * @return bool
	 */
	public function isEntryFile($asset)
	{
		return $this->getEntryFileType($asset) !== null;
	}

	/**
	 * Remove leading loaders from the asset path.
	 *
	 * @param string $asset
	 *
	 * @return string
	 */
	private function removeLoaders($asset)
	{
		$position = strrpos($asset, '!');

		return $position === false ? $asset : substr($asset, $position + 1);
	}

	/**
	 * @param string $extension
	 *
	 * @return bool
	 */
	private function isExtensionIncluded($extension)
	{
		if (count($this->enabledExtensions) === 0) {
			$isExtensionIncluded = count($this->disabledExtensions) > 0 && !in_array($extension, $this->disabledExtensions, true);
		} else {
			$isExtensionIncluded = in_array($extension, $this->enabledExtensions, true);
		}

		return $isExtensionIncluded;
	}

	/**
	 * @param string $extension
	 *
	 * @return int|string
	 */
	private function mapExtension($extension)
	{
		foreach ($this->typeMap as $mappedExtension => $fromExtensions) {

			if (in_array($extension, $fromExtensions, true)) {
				return $mappedExtension;
			}
		}

		return $extension;
	}
}
