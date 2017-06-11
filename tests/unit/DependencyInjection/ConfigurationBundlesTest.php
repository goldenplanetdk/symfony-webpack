<?php

namespace GoldenPlanet\WebpackBundle\Tests\DependencyInjection;

use Codeception\TestCase\Test;
use GoldenPlanet\WebpackBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationBundlesTest extends Test
{
	/**
	 * Test resulting bundles list
	 * according to applied `enabled_bundles` value in custom config.
	 *
	 * @param array      $config
	 * @param array|null $expected
	 *
	 * @dataProvider bundlesResourceDataProvider
	 */
	public function testBundlesResource($config, $expected)
	{
		$bundles = [
			'MyFirstBundle',
			'MySecondBundle',
		];

		$configuration = new Configuration($bundles, 'dev');
		$processor = new Processor();

		$result = $processor->processConfiguration($configuration, [$config]);

		$this->assertSame($expected, $result['enabled_bundles']);
	}

	/**
	 * @return array
	 */
	public function bundlesResourceDataProvider()
	{
		return [
			'`enabled_bundles` not specified: all bundles should be added' => [
				// config.yml
				[],
				// expected bundles list
				[
					'MyFirstBundle',
					'MySecondBundle',
				],
			],
			'`enabled_bundles` is null: all bundles should be added' => [
				// config.yml
				[
					'enabled_bundles' => null,
				],
				// expected bundles list
				[
					'MyFirstBundle',
					'MySecondBundle',
				],
			],
			'`enabled_bundles` are specified: default list of bundles should be overwritten' => [
				// config.yml
				[
					'enabled_bundles' => [
						'MyFirstBundle',
					],
				],
				// expected bundles list
				['MyFirstBundle'],
			],
			'`enabled_bundles` is specified: list of bundles should be empty' => [
				// config.yml
				[
					'enabled_bundles' => [],
				],
				// expected bundles list
				[],
			],
		];
	}
}
