<?php

namespace GoldenPlanet\WebpackBundle;

use Maba\Component\DependencyInjection\AddTaggedCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GoldenPlanetWebpackBundle extends Bundle {

	public function build(ContainerBuilder $container) {

		$container->addCompilerPass(new AddTaggedCompilerPass(
			'gp_webpack.asset_provider.dynamic',
			'gp_webpack.asset_provider',
			'addProvider',
			['type']
		));
	}
}
