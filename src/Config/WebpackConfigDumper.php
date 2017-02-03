<?php

namespace GoldenPlanet\WebpackBundle\Config;

class WebpackConfigDumper {

	private $path;
	private $includeConfigPath;
	private $manifestPath;
	private $environment;
	private $parameters;

	/**
	 * @param string $path              full path where config should be dumped
	 * @param string $includeConfigPath path of config to be included inside dumped config
	 * @param string $manifestPath
	 * @param string $environment
	 * @param array  $parameters
	 */
	public function __construct($path, $includeConfigPath, $manifestPath, $environment, array $parameters) {
		$this->path = $path;
		$this->includeConfigPath = $includeConfigPath;
		$this->manifestPath = $manifestPath;
		$this->environment = $environment;
		$this->parameters = $parameters;
	}

	/**
	 * Wrap configuration with a node module export syntax that invokes
	 * the custom symfony.webpack.config.js with provided configuration
	 * and then returns the complete config to webpack
	 *
	 * @param WebpackConfig $config
	 *
	 * @return string
	 */
	public function dump(WebpackConfig $config) {

		$configTemplate = 'module.exports = require(%s)(%s);';

		$configContents = sprintf(
			$configTemplate,
			json_encode($this->includeConfigPath),
			json_encode([
				'entry'        => $config->getEntryPoints(),
				'alias'        => $config->getAliases(),
				'manifestPath' => $this->manifestPath,
				'environment'  => $this->environment,
				'parameters'   => $this->parameters,
			])
		);

		file_put_contents($this->path, $configContents);

		return $this->path;
	}
}
