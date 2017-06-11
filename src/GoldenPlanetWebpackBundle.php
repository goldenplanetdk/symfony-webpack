<?php

namespace GoldenPlanet\WebpackBundle;

use Maba\Component\DependencyInjection\AddTaggedCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GoldenPlanetWebpackBundle extends Bundle
{
	/**
	 * Services that are tagged with 'gp_webpack.asset_provider'
	 * will be added to `AssetCollector->assetProviders` array
	 *
	 * Tagged services should be added to the asset providers array
	 * with the `AssetCollector->addAssetProvider()` method
	 *
	 * `AssetCollector` is the parent service that hosts all asset providers
	 *
	 * @param ContainerBuilder $container
	 */
	public function build(ContainerBuilder $container)
	{
		$tagName = 'gp_webpack.asset_provider';
		$parentServiceId = 'gp_webpack.asset_collector';
		$methodName = 'addAssetProvider';

		$taggedCompilerPass = new AddTaggedCompilerPass($parentServiceId, $tagName, $methodName);

		$container->addCompilerPass($taggedCompilerPass);
	}
}
