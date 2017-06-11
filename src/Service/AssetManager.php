<?php

namespace GoldenPlanet\WebpackBundle\Service;

use GoldenPlanet\WebpackBundle\Model\WebpackConfigModel;
use RuntimeException;

class AssetManager
{
	const TYPE_JS = 'js';
	const TYPE_CSS = 'css';

	private $ManifestPhpStorage;
	private $manifest = null;
	private $assetNameGenerator;
	private $entryFileManager;

	public function __construct(
		ManifestPhpStorage $ManifestPhpStorage,
		AssetNameGenerator $assetNameGenerator,
		EntryFileManager $entryFileManager
	) {
		$this->ManifestPhpStorage = $ManifestPhpStorage;
		$this->assetNameGenerator = $assetNameGenerator;
		$this->entryFileManager = $entryFileManager;
	}

	/**
	 * Gets URL for specified asset (usually provided to webpack_asset twig function)
	 * If type not specified, it is guessed.
	 *
	 * Exception is thrown if manifest does not exit, asset is not in the manifest or
	 *      type is not provided and cannot be guessed
	 *
	 * @param string      $asset    File path with optionally prepended webpack loaders
	 *                              or custom entry point name
	 * @param string|null $fileType Specifies type in manifest, usually "js" or "css"
	 *
	 * @return null|string null is returned if type is provided and missing in manifest
	 *
	 * @throws RuntimeException
	 *
	 * @api
	 */
	public function getAssetUrl($asset, $fileType = null)
	{
		$assetFileName = $this->assetNameGenerator->generateName($asset);

		$assetManifestEntry = $this->getManifestEntry(
			$assetFileName,
			[
				'assetDescription' => sprintf(
					"'%s' (key '%s' was not found)",
					$asset,
					$assetFileName
				),
			]
		);

		if ($fileType === null) {
			$fileType = $this->guessFileType($assetFileName, $asset, $assetManifestEntry);
		}

		return isset($assetManifestEntry[$fileType]) ? $assetManifestEntry[$fileType] : null;
	}

	/**
	 * Gets URL for specified named asset - should be used for commons chunks.
	 *
	 * Exception is thrown if manifest does not exit or asset is not in the manifest.
	 *
	 * Type is not guessed as commons chunk only has a name and no path.
	 *
	 * @param string      $assetName
	 * @param string|null $fileType specifies type in manifest, usually "js" or "css"
	 *
	 * @return string|null null is returned if type is provided and missing in manifest
	 *
	 * @throws RuntimeException
	 *
	 * @api
	 */
	public function getNamedAssetUrl($assetName, $fileType = null)
	{
		$manifestEntry = $this->getManifestEntry(
			$assetName,
			[
				'errorDescription' => 'This is probably a commons chunk - is it configured by this name in '
					. WebpackConfigModel::WEBPACK_SYMFONY_CONFIG_FILE_NAME . '?',
			]
		);

		if ($fileType === null) {
			$fileType = self::TYPE_JS;
		}

		return isset($manifestEntry[$fileType]) ? $manifestEntry[$fileType] : null;
	}

	/**
	 * @param string $assetName
	 * @param array  $exceptionInfo
	 *        string assetDescription
	 *        string errorDescription
	 *
	 * @return mixed
	 */
	private function getManifestEntry($assetName, $exceptionInfo)
	{
		if ($this->manifest === null) {
			$this->manifest = $this->ManifestPhpStorage->requireManifest();
		}

		if (!isset($this->manifest[$assetName])) {

			$assetDescription = $exceptionInfo['assetDescription'] ?? "'$assetName'";
			$errorDescription = ($exceptionInfo['errorDescription'] ?? '') . ' Is webpack:dev-server running in the background?';

			throw new RuntimeException(
				sprintf('No information in manifest for %s. %s', $assetDescription, trim($errorDescription))
			);
		}

		return $this->manifest[$assetName];
	}

	/**
	 * @param string $assetName
	 * @param string $asset Asset path with optionally prepended webpack loaders
	 * @param string $manifestEntry
	 *
	 * @return int|null|string
	 */
	private function guessFileType($assetName, $asset, $manifestEntry)
	{
		$entryFileType = $this->entryFileManager->getEntryFileType($asset);
		$fileType = $entryFileType !== null ? $entryFileType : self::TYPE_JS;

		if (!isset($manifestEntry[$fileType])) {

			throw new RuntimeException(sprintf(
				"No information in the manifest for file type '%s' (key '%s', required asset '%s'). %s",
				$fileType,
				$assetName,
				$asset,
				'Probably extension is unsupported or some misconfiguration issue. '
				. 'If this file should compile to javascript, please extend '
				. 'entry_file.disabled_extensions in config.yml'
			));
		}

		return $fileType;
	}
}
