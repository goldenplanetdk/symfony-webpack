<?php

namespace GoldenPlanet\WebpackBundle\Webpack\Compiler;

use Closure;
use Exception;
use GoldenPlanet\WebpackBundle\Model\WebpackConfigModel;
use GoldenPlanet\WebpackBundle\Webpack\Config\WebpackConfigManager;
use GoldenPlanet\WebpackBundle\Exception\NoEntryPointsException;
use GoldenPlanet\WebpackBundle\Service\ManifestPhpStorage;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class WebpackCompiler
{
	private $webpackConfigManager;
	private $manifestJsonPath;
	private $manifestPhpStorage;
	private $webpackProcessBuilder;
	private $logger;

	/**
	 * WebpackCompiler constructor.
	 *
	 * @param WebpackConfigManager  $webpackConfigManager
	 * @param string                $manifestJsonPath
	 * @param ManifestPhpStorage    $manifestPhpStorage
	 * @param WebpackProcessBuilder $webpackProcessBuilder
	 * @param LoggerInterface       $logger
	 */
	public function __construct(
		WebpackConfigManager $webpackConfigManager,
		$manifestJsonPath,
		ManifestPhpStorage $manifestPhpStorage,
		WebpackProcessBuilder $webpackProcessBuilder,
		LoggerInterface $logger
	) {
		$this->webpackConfigManager = $webpackConfigManager;
		$this->manifestJsonPath = $manifestJsonPath;
		$this->manifestPhpStorage = $manifestPhpStorage;
		$this->webpackProcessBuilder = $webpackProcessBuilder;
		$this->logger = $logger;
	}

	/**
	 * Actions that are performed before compilation:
	 * - Delete existing `webpack-manifest.json` file
	 * - Dump `webpack.config.json` file
	 *
	 * @param Closure $outputCallback
	 *
	 * @return WebpackConfigModel|null
	 */
	private function dumpWebpackConfig(Closure $outputCallback)
	{
		// Remove manifest file from previous session - make sure we create a new one
		$this->removeManifestJsonFile();

		try {

			$config = $this->webpackConfigManager->collectAndDump();

		} catch (NoEntryPointsException $exception) {

			$this->outputNoEntryPointsNotice($outputCallback);
			return null;
		}

		return $config;
	}

	/**
	 * @param OutputInterface $output
	 */
	public function compile(OutputInterface $output)
	{
		$outputCallback = $this->getCallbackForOutput($output, true);

		$config = $this->dumpWebpackConfig($outputCallback);
		$entryPoints = $config->getEntryPoints();

		// Webpack validates the `entries` property and fails if it's empty
		if (!$entryPoints) {
			return;
		}

		$process = $this->webpackProcessBuilder->buildWebpackProcess($config);

		// remove manifest file if exists - keep sure we create new one
		if (file_exists($this->manifestJsonPath)) {
			unlink($this->manifestJsonPath);
		}

		$this->logger->debug("\ntravis_fold:start:webpack-compile\nwebpack:compile");
		$process->mustRun($outputCallback);
		$this->logger->debug("\ntravis_fold:end:webpack-compile");

		$this->saveManifestPhp();
	}

	/**
	 * @param OutputInterface $output
	 * @param bool            $isDevServer
	 *
	 * @throws Exception
	 */
	public function compileAndWatch(OutputInterface $output, $isDevServer = false)
	{
		$outputCallback = $this->getCallbackForOutput($output);

		$config = $this->dumpWebpackConfig($outputCallback);

		$process = $this->webpackProcessBuilder->buildDevServerProcess($config, $isDevServer);

		if (file_exists($this->manifestJsonPath) && !unlink($this->manifestJsonPath)) {
			throw new RuntimeException('Cannot delete manifest JSON file at ' . $this->manifestJsonPath);
		}

		$webpackCompiler = $this;
		$logger = $this->logger;

		$processCallback = function ($type, $buffer) use ($webpackCompiler, $outputCallback, $logger) {

			$webpackCompiler->saveManifestPhp(false);

			$logger->info('Processing callback from process', [$type, $buffer]);

			if ($outputCallback !== null) {
				$outputCallback($type, $buffer);
			}
		};

		$this->logger->info('Starting process', [$process->getCommandLine()]);
		$process->start($processCallback);

		try {
			$this->loop($process, $config, $processCallback, $outputCallback);
		} catch (Exception $exception) {
			$process->stop();
			throw $exception;
		}
	}

	private function loop(Process $process, WebpackConfigModel $config, $processCallback, $outputCallback)
	{
		while (true) {
			sleep(1);

			$this->logger->debug('Dumping webpack configuration', [$process->getPid()]);

			try {
				$config = $this->webpackConfigManager->collectAndDump($config);

			} catch (NoEntryPointsException $exception) {

				$process->stop();
				$this->outputNoEntryPointsNotice($outputCallback);
				return;
			}

			if ($config->wasFileDumped()) {

				$this->logger->info(
					'File was dumped (configuration changed) - restarting process',
					$config->getEntryPoints()
				);

				$process->stop();
				$process = $process->restart($processCallback);

			} else {

				if (!$process->isRunning()) {
					$this->logger->info('Process has shut down - returning');

					return;
				}

				$process->getOutput();

				// try to save the manifest - output callback is not called in dashboard mode
				$this->saveManifestPhp(false);
			}
		}
	}

	public function saveManifestPhp($failIfMissing = true)
	{
		if (!file_exists($this->manifestJsonPath)) {

			if ($failIfMissing) {

				throw new RuntimeException(
					'Missing manifest file in ' . $this->manifestJsonPath
					. '. Keep sure assets-webpack-plugin is enabled with the same path in webpack config'
				);
			}

			return;
		}

		$manifestJson = file_get_contents($this->manifestJsonPath);
		$manifest = json_decode($manifestJson, true);

		$this->manifestPhpStorage->saveManifest($manifest);
	}

	/**
	 * Remove `webpack-manifest.json` file that was generated by `assets-webpack-plugin`
	 */
	private function removeManifestJsonFile()
	{
		if (file_exists($this->manifestJsonPath)) {

			$this->logger->info('Deleting manifest file', [$this->manifestJsonPath]);

			unlink($this->manifestJsonPath);
		}
	}

	/**
	 * @param Closure|null $outputCallback
	 */
	private function outputNoEntryPointsNotice(Closure $outputCallback = null)
	{
		if ($outputCallback !== null) {

			$outputCallback(Process::OUT, 'No entry points found - not running webpack' . PHP_EOL);
		}
	}

	/**
	 * @param OutputInterface $output
	 * @param bool            $useLogger
	 *
	 * @return Closure
	 */
	private function getCallbackForOutput(OutputInterface $output, $useLogger = false)
	{
		$logger = $useLogger ? $this->logger : null;

		return function ($type, $buffer) use ($output, $logger) {

			if (Process::ERR === $type) {
				$output->write('<error>' . $buffer . '</error>');
			} else {
				$output->write($buffer);
			}

			if ($logger) {

				if (Process::ERR === $type) {
					$logger->error($buffer);
				} else {
					$logger->debug($buffer);
				}
			}
		};
	}
}
