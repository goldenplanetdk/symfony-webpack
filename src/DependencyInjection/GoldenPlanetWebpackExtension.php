<?php

namespace GoldenPlanet\WebpackBundle\DependencyInjection;

use GoldenPlanet\WebpackBundle\Webpack\Compiler\WebpackProcessBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class GoldenPlanetWebpackExtension extends Extension
{
	/**
	 * {@inheritdoc}
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = $this->getConfiguration($configs, $container);
		$config = $this->processConfiguration($configuration, $configs);

		$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
		$loader->load('services.yml');

		// Set parameters based on `golden_planet_webpack` configuration
		$this->configureRoot($container, $config);
		$this->configureEnabledBundles($container, $config);
		$this->configureTwig($container, $config);
		$this->configureConfig($container, $config);
		$this->configureAliases($container, $config);
		$this->configureBin($container, $config);
		$this->configureDashboard($container, $config);
		$this->configureEntryFile($container, $config);
	}

	public function getConfiguration(array $config, ContainerBuilder $container)
	{
		return new Configuration(
			array_keys($container->getParameter('kernel.bundles')),
			$container->getParameter('kernel.environment')
		);
	}

	private function configureRoot(ContainerBuilder $container, array $config)
	{
		$container->setParameter('gp_webpack.cache_directory', $config['cache_directory']);
	}

	private function configureEnabledBundles(ContainerBuilder $container, array $config)
	{
		$container->setParameter('gp_webpack.enabled_bundles', $config['enabled_bundles']);
	}

	private function configureTwig(ContainerBuilder $container, array $config)
	{
		$twigDirectories = $config['twig']['additional_directories'];
		$twigDirectories[] = '%kernel.root_dir%/Resources/views';

		$container->setParameter('gp_webpack.twig_directories', $twigDirectories);

		if ($config['twig']['suppress_errors'] === true) {
			$errorHandlerId = 'gp_webpack.error_handler.suppressing';
		} elseif ($config['twig']['suppress_errors'] === 'ignore_unknowns') {
			$errorHandlerId = 'gp_webpack.error_handler.ignore_unknowns';
		} else {
			$errorHandlerId = 'gp_webpack.error_handler.default';
		}

		$container->setAlias('gp_webpack.error_handler', $errorHandlerId);
	}

	private function configureConfig(ContainerBuilder $container, $config)
	{
		$container->setParameter('gp_webpack.config.path', $config['config']['path']);
		$container->setParameter('gp_webpack.config.parameters', $config['config']['parameters']);
	}

	private function configureAliases(ContainerBuilder $container, $config)
	{
		$additionalAliases =
			$config['aliases']['additional']
			+ [
				'app' => '%kernel.root_dir%/Resources/assets',
				'root' => '%kernel.root_dir%',
			];

		$container->setParameter('gp_webpack.aliases.additional', $additionalAliases);
		$container->setParameter('gp_webpack.aliases.path_in_bundle', $config['aliases']['path_in_bundle']);

		$container->setParameter('gp_webpack.aliases.additional', $additionalAliases);

	}

	private function configureBin(ContainerBuilder $container, $config)
	{
		$container->setParameter('gp_webpack.bin.disable_tty', $config['bin']['disable_tty']);
		$container->setParameter('gp_webpack.bin.working_directory', $config['bin']['working_directory']);
		$container->setParameter('gp_webpack.bin.webpack.executable', $config['bin']['webpack']['executable']);
		$container->setParameter('gp_webpack.bin.webpack.arguments', $config['bin']['webpack']['arguments']);
		$container->setParameter('gp_webpack.bin.dev_server.executable', $config['bin']['dev_server']['executable']);
		$container->setParameter('gp_webpack.bin.dev_server.arguments', $config['bin']['dev_server']['arguments']);
	}

	private function configureDashboard(ContainerBuilder $container, $config)
	{
		$dashboardModeMap = [
			'always' => WebpackProcessBuilder::DASHBOARD_MODE_ENABLED_ALWAYS,
			'dev_server' => WebpackProcessBuilder::DASHBOARD_MODE_ENABLED_ON_DEV_SERVER,
			false => WebpackProcessBuilder::DASHBOARD_MODE_DISABLED,
		];

		$container->setParameter('gp_webpack.dashboard.mode', $dashboardModeMap[$config['dashboard']['enabled']]);
		$container->setParameter('gp_webpack.dashboard.executable', $config['dashboard']['executable']);
	}

	private function configureEntryFile(ContainerBuilder $container, $config)
	{
		if ($config['entry_file']['enabled']) {
			$container->setParameter('gp_webpack.entry_file.disabled_extensions', $config['entry_file']['disabled_extensions']);
			$container->setParameter('gp_webpack.entry_file.enabled_extensions', $config['entry_file']['enabled_extensions']);
		} else {
			// both empty disables the functionality
			$container->setParameter('gp_webpack.entry_file.disabled_extensions', []);
			$container->setParameter('gp_webpack.entry_file.enabled_extensions', []);
		}

		$container->setParameter('gp_webpack.entry_file.type_map', $config['entry_file']['type_map']);
	}
}
