<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel {

	protected $configFile = 'config.yml';

	/**
	 * {@inheritDoc}
	 */
	public function registerBundles() {

		$bundles = [

			new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
			new Symfony\Bundle\TwigBundle\TwigBundle(),
			new Symfony\Bundle\MonologBundle\MonologBundle(),

			new GoldenPlanet\WebpackBundle\GoldenPlanetWebpackBundle(),

			new Fixtures\GoldenPlanet\WebpackTestBundle\GoldenPlanetWebpackTestBundle(),
			new Fixtures\GoldenPlanet\WebpackAnotherTestBundle\GoldenPlanetWebpackAnotherTestBundle(),
		];

		return $bundles;
	}

	/**
	 * Set kernel config file for current test case
	 *
	 * @param string $configFile
	 */
	public function setConfigFile($configFile) {

		$this->configFile = 'config_' . $configFile . '.yml';
	}

	/**
	 * Invoked before the kernel is boooted
	 *
	 * {@inheritDoc}
	 */
	public function registerContainerConfiguration(LoaderInterface $loader) {

		$loader->load(__DIR__ . '/config/' . $this->configFile);
	}
}
