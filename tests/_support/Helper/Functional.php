<?php
namespace Helper;

use Codeception\Lib\Connector\Symfony2 as Symfony2Connector;
use Codeception\Module\Filesystem;
use Codeception\Module\Symfony2;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class Functional extends Symfony2 {

	/**
	 * @var CommandTester
	 */
	protected $commandTester;

	/**
	 * @var int
	 */
	protected $errorCode;

	public function _before(\Codeception\TestCase $test) {
		// do nothing
	}

	/**
	 * Boot a Symfony application with specified config file
	 *
	 * @param string|null $configFile
	 */
	protected function bootKernel($configFile = null) {

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
	public function bootKernelWith($configFile = null) {
		$this->kernel = null;
		$this->bootKernel($configFile);
		$this->container = $this->kernel->getContainer();
		$this->client = new Symfony2Connector($this->kernel);
		$this->client->followRedirects(true);
	}

	public function cleanUp() {

		/** @var Filesystem $filesystem */
		$filesystem = $this->getModule('Filesystem');

		$filePackageJson
			= __DIR__ . '/../../functional/Fixtures/package.json';
		$fileWebpackConfigJs
			= __DIR__ . '/../../functional/Fixtures/app/config/symfony.webpack.config.js';
		$fileWebpackConfigRulesJs
			= __DIR__ . '/../../functional/Fixtures/app/config/webpack-rules.js';

		$dirCache
			= __DIR__ . '/../../functional/Fixtures/app/cache';
		$dirCompiled
			= __DIR__ . '/../../functional/Fixtures/web/compiled';
		$dirCompiledCustom
			= __DIR__ . '/../../functional/Fixtures/web/assets';

		file_exists($filePackageJson) && unlink($filePackageJson);
		file_exists($fileWebpackConfigJs) && unlink($fileWebpackConfigJs);
		file_exists($fileWebpackConfigRulesJs) && unlink($fileWebpackConfigRulesJs);

		file_exists($dirCache) && $filesystem->cleanDir($dirCache);
		file_exists($dirCompiled) && $filesystem->cleanDir($dirCompiled);
		file_exists($dirCompiledCustom) && $filesystem->cleanDir($dirCompiledCustom);
	}

	public function runCommand($commandServiceId, array $input = []) {

		$this->errorCode = null;
		$this->commandTester = null;

		$command = $this->grabServiceFromContainer($commandServiceId);

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
				$exitCode = (int)$exitCode;
				if (0 === $exitCode) {
					$exitCode = 1;
				}
			} else {
				$exitCode = 1;
			}

			$this->errorCode = $exitCode;
			$this->debug((string)$e);

			return;
		}

		$this->debug($commandTester->getDisplay());

		$this->commandTester = $commandTester;
	}

	public function seeCommandStatusCode($code) {
		$statusCode = $this->errorCode !== null ? $this->errorCode : $this->commandTester->getStatusCode();
		$this->assertEquals($code, $statusCode);
	}

	public function seeInCommandDisplay($substring) {
		$this->assertContains($substring, $this->commandTester->getDisplay());
	}

	public function dontSeeInCommandDisplay($substring) {
		$this->assertNotContains($substring, $this->commandTester->getDisplay());
	}

	public function seeFileIsSmallerThan($smallerFilePath, $largerFilePath) {
		if (filesize($smallerFilePath) >= filesize($largerFilePath)) {
			$this->fail("$smallerFilePath is not smaller than $largerFilePath");
		}
	}
}
