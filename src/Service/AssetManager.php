<?php

namespace GoldenPlanet\WebpackBundle\Service;

use RuntimeException;

class AssetManager {

	const TYPE_JS = 'js';
	const TYPE_CSS = 'css';

	private $manifestStorage;
	private $manifest = null;
	private $assetNameGenerator;
	private $entryFileManager;

	public function __construct(
		ManifestStorage $manifestStorage,
		AssetNameGenerator $assetNameGenerator,
		EntryFileManager $entryFileManager
	) {
		$this->manifestStorage = $manifestStorage;
		$this->assetNameGenerator = $assetNameGenerator;
		$this->entryFileManager = $entryFileManager;
	}

	/**
	 * Gets URL for specified asset (usually provided to webpack_asset twig function)
	 * If type not specified, it is guessed
	 *
	 * Exception is thrown if manifest does not exit, asset is not in the manifest or
	 *      type is not provided and cannot be guessed
	 *
	 * @param string      $asset       file path or custom entry point name
	 * @param string|null $fileType    specifies type in manifest, usually "js" or "css"
	 * @param bool        $isEntryName it is a custom entry point name, that is manually specified
	 *                                 in webpack config (e.g. name of a commons chunk)
	 *
	 * @return null|string null is returned if type is provided and missing in manifest
	 *
	 * @api
	 */
	public function getAssetUrl($asset, $fileType = null, $isEntryName = false) {

		$manifest = $this->getManifest();

		if ($isEntryName) {
			$assetName = $asset;
		} else {
			$assetName = $this->assetNameGenerator->generateName($asset);
		}

		if (!isset($manifest[$assetName])) {
			
			throw new RuntimeException(sprintf(
				"No information in manifest for %s '%s' (key '%s' was not found). %s",
				($isEntryName ? 'named entry' : 'file'),
				$asset,
				$assetName,
				'Is webpack:dev-server running in the background?'
			));
		}

		if ($fileType === null) {

			$entryFileType = $this->entryFileManager->getEntryFileType($asset);
			$fileType = $entryFileType ?: self::TYPE_JS;

			if (!isset($manifest[$assetName][$fileType])) {

				throw new RuntimeException(sprintf(
					"No information in the manifest for '%s' file type (key '%s', asset '%s'). %s",
					$fileType,
					$assetName,
					$asset,
					'Probably extension is unsupported or some misconfiguration issue. '
					. 'If this file should compile to javascript, please extend '
					. 'entry_file.disabled_extensions in config.yml'
				));
			}
		}

		$assetUrl = isset($manifest[$assetName][$fileType]) ? $manifest[$assetName][$fileType] : null;

		return $assetUrl;
	}

	private function getManifest() {
		if ($this->manifest === null) {
			$this->manifest = $this->manifestStorage->getManifest();
		}
		return $this->manifest;
	}
}
