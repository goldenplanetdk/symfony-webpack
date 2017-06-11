<?php

namespace GoldenPlanet\WebpackBundle\Webpack\Config;

use GoldenPlanet\WebpackBundle\Model\WebpackConfigModel;

class WebpackConfigDumper
{
	private $path;
	private $includeConfigPath;
	private $manifestPath;
	private $environment;
	private $parameters;

	/**
	 * @param string $path              Full path where config should be dumped
	 * @param string $includeConfigPath Path of config to be included inside dumped config
	 * @param string $manifestPath
	 * @param string $environment
	 * @param array  $parameters
	 */
	public function __construct($path, $includeConfigPath, $manifestPath, $environment, array $parameters)
	{
		$this->path = $path;
		$this->includeConfigPath = $includeConfigPath;
		$this->manifestPath = $manifestPath;
		$this->environment = $environment;
		$this->parameters = $parameters;
	}

	/**
	 * Wrap configuration with a node module export syntax that invokes
	 * the custom webpack.symfony.config.js with provided configuration
	 * and then returns the complete config to webpack.
	 *
	 * @param WebpackConfigModel $config
	 *
	 * @return string
	 */
	public function dump(WebpackConfigModel $config)
	{
		$webpackConfigJsTemplate = 'module.exports = require(%s)(%s);';

		$webpackConfigContents = sprintf(
			$webpackConfigJsTemplate,
			json_encode($this->includeConfigPath),
			json_encode([
				'entry' => (object) $config->getEntryPoints(),
				'groups' => (object) $config->getAssetGroups(),
				'alias' => (object) $config->getAliases(),
				'manifestPath' => $this->manifestPath,
				'environment' => $this->environment,
				'parameters' => (object) $this->parameters,
			])
		);

		file_put_contents($this->path, $webpackConfigContents);
	}
}
