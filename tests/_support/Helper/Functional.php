<?php

namespace Helper;

use Codeception\Lib\Connector\Symfony as SymfonyConnector;
use Codeception\Module\Filesystem;
use Codeception\Module\Symfony2;
use Codeception\TestInterface;
use GoldenPlanet\WebpackBundle\Model\WebpackConfigModel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class Functional extends Symfony2
{
	const WEBPACK_DEFAULT_CONFIG_FILE_NAME = 'webpack.default.config.js';
	const WEBPACK_SYMFONY_CONFIG_FILE_NAME = WebpackConfigModel::WEBPACK_SYMFONY_CONFIG_FILE_NAME;

	/**
	 * @var CommandTester
	 */
	protected $commandTester;

	/**
	 * @var int
	 */
	protected $errorCode;

	public function _before(TestInterface $test)
	{
		// do nothing
	}

	/**
	 * Boot a Symfony application with specified config file
	 *
	 * @param string|null $configFile
	 */
	protected function bootKernel($configFile = null)
	{

		if ($this->kernel) {
			return;
		}

		$this->kernel = new \TestKernel(
			$this->config['environment'] . ($configFile !== null ? $configFile : ''),
			$this->config['debug']
		);

		if ($configFile) {
			$this->kernel->setConfigFile($configFile);
		}

		$this->kernel->boot();
	}

	/**
	 * Boot a Symfony application with specified config file
	 *
	 * $I->bootKernelWith('tags') will boot the app with config_tags.yml
	 * When config is not specified the default config.yml is used
	 *
	 * @param string|null $configFile
	 */
	public function bootKernelWith($configFile = null)
	{
		$this->kernel = null;
		$this->bootKernel($configFile);
		$this->container = $this->kernel->getContainer();
		$this->client = new SymfonyConnector($this->kernel);
		$this->client->followRedirects(true);
	}

	public function cleanUp()
	{
		$this->removeFile(__DIR__ . '/../../functional/Fixtures/package.json');

		$this->removeFile(__DIR__ . '/../../functional/Fixtures/app/config/' . self::WEBPACK_DEFAULT_CONFIG_FILE_NAME);
		$this->removeFile(__DIR__ . '/../../functional/Fixtures/app/config/' . WebpackConfigModel::WEBPACK_SYMFONY_CONFIG_FILE_NAME);

		$this->removeDir(__DIR__ . '/../../functional/Fixtures/app/cache');
		$this->removeDir(__DIR__ . '/../../functional/Fixtures/web/compiled');
		$this->removeDir(__DIR__ . '/../../functional/Fixtures/web/assets');
	}

	/**
	 * Overwrite webpack.symfony.config.js with specified config
	 * That config must require the default config with `require('./webpack.default.config.js')`
	 *
	 * @param string $configName
	 */
	public function extendSymfonyWebpackConfig($configName)
	{
		$dirFixturesAppConfig = __DIR__ . '/../../functional/Fixtures/app/config';

		chdir($dirFixturesAppConfig);

		// At this point the webpack config must already reside in the `config` folder
		// after the `webpack:setup` command that should've been launched in the test

		// Rename the `symfony.config` to a `default.config` that is required in `[$configName].config`
		rename(
			WebpackConfigModel::WEBPACK_SYMFONY_CONFIG_FILE_NAME,
			self::WEBPACK_DEFAULT_CONFIG_FILE_NAME
		);

		// Copy specified config file with the `webpack.symfony.config.js` name
		// That is the default name for `@gp_webpack.config.path` parameter
		copy(
			"webpack/webpack.$configName.config.js",
			WebpackConfigModel::WEBPACK_SYMFONY_CONFIG_FILE_NAME
		);

	}

	public function runCommand($commandServiceId, array $input = [])
	{

		$this->errorCode = null;
		$this->commandTester = null;

		$command = $this->grabService($commandServiceId);

		$application = new Application($this->kernel);
		$application->add($command);

		$commandTester = new CommandTester($command);

		try {
			$commandTester->execute(
				['command' => $command->getName()] + $input,
				['interactive' => false]
			);
		} catch (\Exception $e) {

			$exitCode = $e->getCode();

			if (is_numeric($exitCode)) {
				$exitCode = (int) $exitCode;
				if (0 === $exitCode) {
					$exitCode = 1;
				}
			} else {
				$exitCode = 1;
			}

			$this->errorCode = $exitCode;
			$this->debug((string) $e);

			return;
		}

		$this->debug($commandTester->getDisplay());

		$this->commandTester = $commandTester;
	}

	public function seeCommandStatusCode($code)
	{
		$statusCode = $this->errorCode !== null ? $this->errorCode : $this->commandTester->getStatusCode();
		$this->assertEquals($code, $statusCode);
	}

	public function seeInCommandDisplay($substring)
	{
		$this->assertContains($substring, $this->commandTester->getDisplay());
	}

	public function dontSeeInCommandDisplay($substring)
	{
		$this->assertNotContains($substring, $this->commandTester->getDisplay());
	}

	public function seeFileIsSmallerThan($smallerFilePath, $largerFilePath)
	{
		if (filesize($smallerFilePath) >= filesize($largerFilePath)) {
			$this->fail("$smallerFilePath is not smaller than $largerFilePath");
		}
	}

	/**
	 * Little helper for file removal
	 *
	 * @param string $file
	 */
	private function removeFile($file)
	{
		file_exists($file) && unlink($file);
	}

	/**
	 * Little helper for recursive directory removal
	 *
	 * @param string $dir
	 */
	private function removeDir($dir)
	{
		/** @var Filesystem $filesystem */
		$filesystem = $this->getModule('Filesystem');

		file_exists($dir) && $filesystem->cleanDir($dir);
	}

}
