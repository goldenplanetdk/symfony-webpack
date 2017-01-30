<?php

namespace GoldenPlanet\WebpackBundle\Config;

class WebpackConfig {

	/**
	 * @var array
	 */
	protected $entryPoints;

	/**
	 * @var array
	 */
	protected $aliases;

	/**
	 * @var mixed
	 */
	protected $cacheContext;

	/**
	 * @var string
	 */
	protected $configPath;

	/**
	 * @var boolean
	 */
	protected $fileDumped = false;

	/**
	 * @return array
	 */
	public function getEntryPoints() {
		return $this->entryPoints;
	}

	/**
	 * @param array $entryPoints
	 *
	 * @return $this
	 */
	public function setEntryPoints(array $entryPoints) {
		$this->entryPoints = $entryPoints;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getAliases() {
		return $this->aliases;
	}

	/**
	 * @param array $aliases
	 *
	 * @return $this
	 */
	public function setAliases(array $aliases) {
		$this->aliases = $aliases;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCacheContext() {
		return $this->cacheContext;
	}

	/**
	 * @param mixed $cacheContext
	 *
	 * @return $this
	 */
	public function setCacheContext($cacheContext) {
		$this->cacheContext = $cacheContext;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getConfigPath() {
		return $this->configPath;
	}

	/**
	 * @param string $configPath
	 */
	public function setConfigPath($configPath) {
		$this->configPath = $configPath;
	}

	/**
	 * @return boolean
	 */
	public function wasFileDumped() {
		return $this->fileDumped;
	}

	/**
	 * @param boolean $fileDumped
	 */
	public function setFileDumped($fileDumped) {
		$this->fileDumped = $fileDumped;
	}
}
