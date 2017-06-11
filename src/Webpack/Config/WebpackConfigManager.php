<?php

namespace GoldenPlanet\WebpackBundle\Webpack\Config;

use GoldenPlanet\WebpackBundle\ErrorHandler\ErrorHandlerInterface;
use GoldenPlanet\WebpackBundle\Exception\AssetNotFoundException;
use GoldenPlanet\WebpackBundle\Exception\NoEntryPointsException;
use GoldenPlanet\WebpackBundle\Model\WebpackConfigModel;
use GoldenPlanet\WebpackBundle\Provider\AliasProvider;
use GoldenPlanet\WebpackBundle\Service\AssetCollector;
use GoldenPlanet\WebpackBundle\Service\AssetNameGenerator;
use GoldenPlanet\WebpackBundle\Service\AssetResolver;

class WebpackConfigManager
{
	const DEFAULT_GROUP_NAME = 'default';

	private $aliasProvider;
	private $assetCollector;
	private $configDumper;
	private $configPath;
	private $assetResolver;
	private $assetNameGenerator;
	private $errorHandler;

	public function __construct(
		AliasProvider $aliasProvider,
		AssetCollector $assetCollector,
		WebpackConfigDumper $configDumper,
		string $configPath,
		AssetResolver $assetResolver,
		AssetNameGenerator $assetNameGenerator,
		ErrorHandlerInterface $errorHandler
	) {
		$this->aliasProvider = $aliasProvider;
		$this->assetCollector = $assetCollector;
		$this->configDumper = $configDumper;
		$this->configPath = $configPath;
		$this->assetResolver = $assetResolver;
		$this->assetNameGenerator = $assetNameGenerator;
		$this->errorHandler = $errorHandler;
	}

	/**
	 * Generate main `webpack.config.js`
	 * which requires the `webpack.symfony.config.js`
	 *
	 * Collects all data for the webpack config's `options` object:
	 * - Aliases
	 * - Entry points
	 * - Commons chunk groups
	 *
	 * @param WebpackConfigModel $previousConfig Provided when called in a loop
	 *                                           in watch mode (when watching TWIG files for changes)
	 *
	 * @return WebpackConfigModel
	 */
	public function collectAndDump(WebpackConfigModel $previousConfig = null)
	{
		$aliases = $this->aliasProvider->getAliases();

		$assetCollection = $this->assetCollector
			->getAssets(
				$previousConfig !== null ? $previousConfig->getCacheContext() : null
			);

		$entryPoints = [];
		$assetGroups = [];

		// Collect entry points
		foreach ($assetCollection->getAssets() as $asset) {
			
			$assetPath = $asset->getResource();
			$assetName = $this->assetNameGenerator->generateName($assetPath);

			try {
				$entryPoints[$assetName] = $this->assetResolver->resolveAsset($assetPath);
			} catch (AssetNotFoundException $exception) {
				$this->errorHandler->processException($exception);
			}

			$groupName = $asset->getGroup() !== null ? $asset->getGroup() : self::DEFAULT_GROUP_NAME;
			$assetGroups[$groupName][] = $assetName;
		}

		if (count($entryPoints) === 0) {
			throw new NoEntryPointsException();
		}

		$config = new WebpackConfigModel();
		$config->setAliases($aliases);
		$config->setAssetGroups($assetGroups);
		$config->setEntryPoints($entryPoints);
		$config->setCacheContext($assetCollection->getContext());

		if (
			$previousConfig === null
			|| $aliases !== $previousConfig->getAliases()
			|| $assetGroups !== $previousConfig->getAssetGroups()
			|| $entryPoints !== $previousConfig->getEntryPoints()
			|| !file_exists($previousConfig->getConfigPath())
		) {
			$this->configDumper->dump($config);
			$config->setConfigPath($this->configPath);
			$config->setFileDumped(true);
		} else {
			$config->setConfigPath($previousConfig->getConfigPath());
		}

		return $config;
	}
}
