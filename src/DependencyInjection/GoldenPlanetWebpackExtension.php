<?php

namespace GoldenPlanet\WebpackBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class GoldenPlanetWebpackExtension extends Extension {

	/**
	 * {@inheritdoc}
	 */
	public function load(array $configs, ContainerBuilder $container) {

		$configuration = $this->getConfiguration($configs, $container);
		$config = $this->processConfiguration($configuration, $configs);

		$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
		$loader->load('services.yml');

		$additionalAliases = $config['aliases']['additional'] + ['root' => '%kernel.root_dir%/..'];

		$container->setParameter('gp_webpack.asset_providers', $config['asset_providers']);
		$container->setParameter('gp_webpack.cache_dir', $config['cache_dir']);
		$container->setParameter('gp_webpack.working_dir', $config['working_dir']);
		$container->setParameter('gp_webpack.config.path', $config['config']['path']);
		$container->setParameter('gp_webpack.config.parameters', $config['config']['parameters']);

		if ($config['entry_file']['enabled']) {
			$container->setParameter('gp_webpack.entry_file.disabled_extensions', $config['entry_file']['disabled_extensions']);
			$container->setParameter('gp_webpack.entry_file.enabled_extensions', $config['entry_file']['enabled_extensions']);
		} else {
			$container->setParameter('gp_webpack.entry_file.disabled_extensions', []);
			$container->setParameter('gp_webpack.entry_file.enabled_extensions', []);
		}
		$container->setParameter('gp_webpack.entry_file.type_map', $config['entry_file']['type_map']);

		$container->setParameter('gp_webpack.aliases.register_bundles', $config['aliases']['register_bundles']);
		$container->setParameter('gp_webpack.aliases.bundle_default', $config['aliases']['bundle_default']);
		$container->setParameter('gp_webpack.aliases.additional', $additionalAliases);

		$container->setParameter('gp_webpack.bin.disable_tty', $config['bin']['disable_tty']);
		$container->setParameter('gp_webpack.bin.webpack.executable', $config['bin']['webpack']['executable']);
		$container->setParameter('gp_webpack.bin.webpack.tty_prefix', $config['bin']['webpack']['tty_prefix']);
		$container->setParameter('gp_webpack.bin.webpack.arguments', $config['bin']['webpack']['arguments']);
		$container->setParameter('gp_webpack.bin.dev_server.executable', $config['bin']['dev_server']['executable']);
		$container->setParameter('gp_webpack.bin.dev_server.tty_prefix', $config['bin']['dev_server']['tty_prefix']);
		$container->setParameter('gp_webpack.bin.dev_server.arguments', $config['bin']['dev_server']['arguments']);

		if ($config['twig']['suppress_errors'] === true) {
			$errorHandlerId = 'gp_webpack.error_handler.suppressing';
		} elseif ($config['twig']['suppress_errors'] === 'ignore_unknowns') {
			$errorHandlerId = 'gp_webpack.error_handler.ignore_unknowns';
		} else {
			$errorHandlerId = 'gp_webpack.error_handler.default';
		}
		$container->setAlias('gp_webpack.error_handler', $errorHandlerId);
	}

	public function getConfiguration(array $config, ContainerBuilder $container) {
		return new Configuration(
			array_keys($container->getParameter('kernel.bundles')),
			$container->getParameter('kernel.environment')
		);
	}
}
