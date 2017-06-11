<?php

namespace GoldenPlanet\WebpackBundle\Model;

class WebpackConfigModel
{
	const WEBPACK_SYMFONY_CONFIG_FILE_NAME = 'webpack.symfony.config.js';

	/**
	 * @var array
	 */
	private $entryPoints;

	/**
	 * @var array
	 */
	private $aliases;

	/**
	 * @var array
	 */
	private $assetGroups;

	/**
	 * @var mixed
	 */
	private $cacheContext;

	/**
	 * @var string
	 */
	private $configPath;

	/**
	 * @var bool
	 */
	private $fileDumped = false;

	/**
	 * @return array
	 */
	public function getEntryPoints()
	{
		return $this->entryPoints;
	}

	/**
	 * @param array $entryPoints
	 *
	 * @return $this
	 */
	public function setEntryPoints(array $entryPoints)
	{
		$this->entryPoints = $entryPoints;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getAliases()
	{
		return $this->aliases;
	}

	/**
	 * @param array $aliases
	 *
	 * @return $this
	 */
	public function setAliases(array $aliases)
	{
		$this->aliases = $aliases;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getAssetGroups()
	{
		return $this->assetGroups;
	}

	/**
	 * @param array $assetGroups
	 */
	public function setAssetGroups(array $assetGroups)
	{
		$this->assetGroups = $assetGroups;
	}

	/**
	 * @return mixed
	 */
	public function getCacheContext()
	{
		return $this->cacheContext;
	}

	/**
	 * @param mixed $cacheContext
	 *
	 * @return $this
	 */
	public function setCacheContext($cacheContext)
	{
		$this->cacheContext = $cacheContext;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getConfigPath()
	{
		return $this->configPath;
	}

	/**
	 * @param string $configPath
	 */
	public function setConfigPath($configPath)
	{
		$this->configPath = $configPath;
	}

	/**
	 * @return bool
	 */
	public function wasFileDumped()
	{
		return $this->fileDumped;
	}

	/**
	 * @param bool $fileDumped
	 */
	public function setFileDumped($fileDumped)
	{
		$this->fileDumped = $fileDumped;
	}
}
