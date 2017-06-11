<?php

namespace GoldenPlanet\WebpackBundle\Provider\AssetProvider;

use GoldenPlanet\WebpackBundle\Provider\DirectoryProvider\DirectoryProviderInterface;
use GoldenPlanet\WebpackBundle\Model\AssetCollectionModel;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class TwigAssetsFromDirectoriesProvider implements AssetsFromDirectoriesProviderInterface
{
	/** @var TwigAssetsFromFileProvider */
	private $twigAssetProvider;

	/** @var string Usually `*.twig` only, but can be overridden in a custom directory asset provider */
	private $pattern;

	/** @var DirectoryProviderInterface */
	private $directoryProvider;


	public function __construct(
		TwigAssetsFromFileProvider $twigAssetProvider,
		$pattern,
		DirectoryProviderInterface $directoryProvider
	) {
		$this->twigAssetProvider = $twigAssetProvider;
		$this->pattern = $pattern;
		$this->directoryProvider = $directoryProvider;
	}

	/**
	 * Find twig files with every directory provider
	 * Then parse and find webpack assets in those twig files
	 *
	 * @param array|null $previousContext Former assets collection data
	 *                                    Each item in the array is a file with it's own collection data
	 *
	 * @return AssetCollectionModel
	 */
	public function getAssets($previousContext = null)
	{
		$files = [];

		foreach ($this->directoryProvider->getDirectories() as $directory) {

			foreach ($this->createFinder($directory) as $file) {

				$files[] = $file->getRealPath();
			}
		}

		$assetsFromDirectories = new AssetCollectionModel();
		$contexts = [];

		foreach ($files as $filePath) {

			// Tokenize and parse twig files to find webpack assets
			$assetCollection = $this->twigAssetProvider->getAssets(
				$filePath,
				isset($previousContext[$filePath]) ? $previousContext[$filePath] : null
			);

			$contexts[$filePath] = $assetCollection->getContext();
			$assetsFromDirectories->addAssets($assetCollection->getAssets());
		}

		$assetsFromDirectories->setContext($contexts);

		return $assetsFromDirectories;
	}

	/**
	 * @param string $directory Directory file path
	 *
	 * @return Finder|SplFileInfo[]
	 */
	private function createFinder($directory)
	{
		if (!is_dir($directory)) {
			return [];
		}

		$finder = Finder::create()
			->in($directory)
			->followLinks()
			->files()
			->name($this->pattern)
		;

		return $finder;
	}
}
