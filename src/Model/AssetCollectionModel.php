<?php

namespace GoldenPlanet\WebpackBundle\Model;

class AssetCollectionModel
{
	/**
	 * @var AssetItemModel[]
	 */
	private $assets = [];

	/**
	 * This will contain modification time and the array of collected assets
	 * It is required for caching purposes, to avoid restarting the webpack process
	 *
	 * @var array
	 */
	private $context;

	/**
	 * @return AssetItemModel[]
	 */
	public function getAssets()
	{
		return $this->assets;
	}

	/**
	 * @param array|AssetItemModel[] $assets
	 *
	 * @return AssetCollectionModel
	 */
	public function setAssets(array $assets)
	{
		$this->assets = $assets;

		return $this;
	}

	/**
	 * @param AssetItemModel $asset
	 *
	 * @return $this
	 */
	public function addAsset(AssetItemModel $asset)
	{
		$this->assets[] = $asset;

		return $this;
	}

	/**
	 * @param array|AssetItemModel[] $assets Array of strings
	 *
	 * @return $this
	 */
	public function addAssets(array $assets)
	{
		$this->assets = array_merge($this->assets, $assets);

		return $this;
	}

	/**
	 * @return array
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * @param asset $context
	 *
	 * @return AssetCollectionModel
	 */
	public function setContext($context)
	{
		$this->context = $context;

		return $this;
	}
}
