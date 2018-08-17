<?php

namespace GoldenPlanet\WebpackBundle\DependencyInjection;

use GoldenPlanet\WebpackBundle\Model\WebpackConfigModel;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link * http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
	/**
	 * All Symfony bundles that are registered in `[App]Kernel.php` (including vendor)
	 *
	 * @var array
	 */
	private $availableBundles;

	/**
	 * @var string
	 */
	private $environment;

	/**
	 * Configuration constructor.
	 *
	 * @param array  $availableBundles
	 * @param string $environment
	 */
	public function __construct(array $availableBundles, $environment)
	{
		$this->availableBundles = $availableBundles;
		$this->environment = $environment;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('golden_planet_webpack');
		$rootChildren = $rootNode->children();

		$this->configureRoot($rootChildren);
		$this->configureEnabledBundles($rootChildren);
		$this->configureTwig($rootChildren);
		$this->configureConfig($rootChildren);
		$this->configureAliases($rootChildren);
		$this->configureBin($rootChildren);
		$this->configureDashboard($rootChildren);
		$this->configureEntryFile($rootChildren);

		return $treeBuilder;
	}

	/**
	 * Options in root of configuration tree
	 *
	 * Working and cache directories
	 *
	 * @param NodeBuilder $rootChildren
	 */
	private function configureRoot(NodeBuilder $rootChildren)
	{
		$rootChildren
			->scalarNode('cache_directory')
			->defaultValue('%kernel.cache_dir%')
		;
	}

	/**
	 * Paths to bundles that should be scanned for entry points
	 *
	 * @param NodeBuilder $rootChildren
	 */
	private function configureEnabledBundles(NodeBuilder $rootChildren)
	{
		$rootChildren
			->arrayNode('enabled_bundles')
			->defaultValue($this->availableBundles)
			->treatNullLike($this->availableBundles)
			->prototype('scalar')
		;;
	}

	/**
	 * Twig error handling
	 *
	 * @param NodeBuilder $rootChildren
	 */
	private function configureTwig(NodeBuilder $rootChildren)
	{
		$twigNodeChildren = $rootChildren
			->arrayNode('twig')
			->addDefaultsIfNotSet()
			->children()
		;

		$twigNodeChildren
			->arrayNode('additional_directories')
			->prototype('scalar')
		;

		$twigNodeChildren
			->scalarNode('suppress_errors')
			->defaultValue(
				$this->environment === 'dev' ? true : 'ignore_unknowns'
			)
			->validate()
			->ifNotInArray([
				true,
				false,
				'ignore_unknowns',
			])
			->thenInvalid('suppress_errors must be either a boolean or "ignore_unknowns"')
		;
	}

	/**
	 * Webpack config path and parameters
	 *
	 * @param NodeBuilder $rootChildren
	 */
	private function configureConfig(NodeBuilder $rootChildren)
	{
		$configNodeChildren = $rootChildren
			->arrayNode('config')
			->addDefaultsIfNotSet()
			->children()
		;

		$configNodeChildren
			->scalarNode('path')
			->defaultValue('%kernel.root_dir%/config/' . WebpackConfigModel::WEBPACK_SYMFONY_CONFIG_FILE_NAME)
		;

		$configNodeChildren
			->arrayNode('parameters')
			->treatNullLike([])
			->useAttributeAsKey('name')
			->prototype('variable')
		;
	}

	/**
	 * Aliases configuration
	 *
	 * @param NodeBuilder $rootChildren
	 */
	private function configureAliases(NodeBuilder $rootChildren)
	{
		$aliasesNodeChildren = $rootChildren
			->arrayNode('aliases')
			->addDefaultsIfNotSet()
			->children()
		;

		$aliasesNodeChildren
			->scalarNode('path_in_bundle')
			->defaultValue('Resources/assets')
		;

		$aliasesNodeChildren
			->arrayNode('additional')
			->treatNullLike([])
			->useAttributeAsKey('name')
			->prototype('scalar')
		;
	}

	/**
	 * Webpack and webpack-dev-server executables
	 *
	 * @param NodeBuilder $rootChildren
	 */
	private function configureBin(NodeBuilder $rootChildren)
	{
		$binNodeChildren = $rootChildren
			->arrayNode('bin')
			->addDefaultsIfNotSet()
			->children()
		;

		/**
		 * bin
		 */
		$binNodeChildren
			->booleanNode('disable_tty')
			->defaultValue($this->environment !== 'dev')
		;

		$binNodeChildren
			->scalarNode('working_directory')
			->defaultValue('%kernel.root_dir%')
		;

		/**
		 * bin.webpack
		 */
		$webpackNodeChildren = $binNodeChildren
			->arrayNode('webpack')
			->addDefaultsIfNotSet()
			->children()
		;

		$webpackNodeChildren
			->arrayNode('executable')
			->defaultValue(['node_modules/.bin/webpack'])
			->prototype('scalar')
		;

		$webpackNodeChildren
			->arrayNode('arguments')
			->defaultValue([])
			->prototype('scalar')
		;

		/**
		 * bin.dev_server
		 */
		$devServerNodeChildren = $binNodeChildren
			->arrayNode('dev_server')
			->addDefaultsIfNotSet()
			->children()
		;

		$devServerNodeChildren
			->arrayNode('executable')
			->defaultValue(['node_modules/.bin/webpack-dev-server'])
			->prototype('scalar')
		;

		$devServerNodeChildren
			->arrayNode('arguments')
			->defaultValue([
				'--hot',
				'--history-api-fallback',
				'--inline',
			])
			->prototype('scalar')
		;
	}

	private function configureDashboard(NodeBuilder $rootChildren)
	{
		$dashboardNodeChildren = $rootChildren
			->arrayNode('dashboard')
			->addDefaultsIfNotSet()
			->children()
		;

		$dashboardNodeChildren
			->scalarNode('enabled')
			->defaultValue('dev_server')
			->validate()
			->ifNotInArray([
				'dev_server',
				'always',
				false,
			])
			->thenInvalid('enabled must be one of "dev_server", "always" or a boolean false')
		;

		$dashboardNodeChildren
			->arrayNode('executable')
			->defaultValue(['node_modules/.bin/webpack-dashboard'])
			->prototype('scalar')
		;
	}

	/**
	 * Webpack entry files configuration
	 *
	 * @param NodeBuilder $rootChildren
	 */
	private function configureEntryFile(NodeBuilder $rootChildren)
	{
		$entryFileNodeChildren = $rootChildren
			->arrayNode('entry_file')
			->addDefaultsIfNotSet()
			->children()
		;

		$entryFileNodeChildren
			->booleanNode('enabled')
			->defaultTrue()
		;

		$entryFileNodeChildren
			->arrayNode('disabled_extensions')
			->defaultValue([
				'js',
				'jsx',
				'ts',
				'coffee',
				'es6',
				'ls',
			])
			->prototype('scalar')
			->info('For these extensions default webpack functionality will be used')
		;

		$entryFileNodeChildren
			->arrayNode('enabled_extensions')
			->defaultValue([])
			->prototype('scalar')
			->info(
				'For these extensions file itself will be provided (not JS file). '
				. 'Set to non-empty to override disabled extensions. Empty means all but disabled'
			)
		;

		$entryFileNodeChildren
			->arrayNode('type_map')
			->defaultValue([
				'css' => [
					'less',
					'scss',
					'sass',
					'styl',
				],
			])
			->prototype('array')
			->info(
				'What output file type to use for what input file types. Used only for entry files. '
				. 'Defaults to same file type - needed only when preprocessors are used'
			)
		;
	}
}
