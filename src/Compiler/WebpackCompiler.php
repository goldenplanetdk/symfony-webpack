<?php

namespace GoldenPlanet\WebpackBundle\Compiler;

use Closure;
use Exception;
use GoldenPlanet\WebpackBundle\Config\WebpackConfig;
use GoldenPlanet\WebpackBundle\Config\WebpackConfigManager;
use GoldenPlanet\WebpackBundle\Service\ManifestStorage;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Process\Exception\RuntimeException as ProcessRuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class WebpackCompiler {

	private $webpackConfigManager;
	private $manifestJsonPath;
	private $manifestStorage;
	private $workingDirectory;
	private $logger;
	private $webpackExecutable;
	private $webpackTtyPrefix;
	private $webpackArguments;
	private $devServerExecutable;
	private $devServerTtyPrefix;
	private $devServerArguments;
	private $disableTty;

	/**
	 * WebpackCompiler constructor.
	 *
	 * @param WebpackConfigManager $webpackConfigManager
	 * @param string               $manifestPath
	 * @param ManifestStorage      $manifestStorage
	 * @param string               $workingDirectory
	 * @param LoggerInterface      $logger
	 * @param array                $webpackExecutable
	 * @param array                $webpackTtyPrefix
	 * @param array                $webpackArguments
	 * @param array                $devServerExecutable
	 * @param array                $devServerTtyPrefix
	 * @param array                $devServerArguments
	 * @param bool                 $disableTty
	 */
	public function __construct(
		WebpackConfigManager $webpackConfigManager,
		$manifestPath,
		ManifestStorage $manifestStorage,
		$workingDirectory,
		LoggerInterface $logger,
		array $webpackExecutable,
		array $webpackTtyPrefix,
		array $webpackArguments,
		array $devServerExecutable,
		array $devServerTtyPrefix,
		array $devServerArguments,
		$disableTty
	) {
		$this->webpackConfigManager = $webpackConfigManager;
		$this->manifestJsonPath = $manifestPath;
		$this->manifestStorage = $manifestStorage;
		$this->workingDirectory = $workingDirectory;
		$this->logger = $logger;
		$this->webpackExecutable = $webpackExecutable;
		$this->webpackTtyPrefix = $webpackTtyPrefix;
		$this->webpackArguments = $webpackArguments;
		$this->devServerExecutable = $devServerExecutable;
		$this->devServerTtyPrefix = $devServerTtyPrefix;
		$this->devServerArguments = $devServerArguments;
		$this->disableTty = $disableTty;
	}

	public function compile(Closure $callback = null) {

		$config = $this->webpackConfigManager->dump();
		$entryPoints = $config->getEntryPoints();

		if (!$entryPoints) {
			// Webpack 2.x validates the entries property and fails if it's empty
			return;
		}

		$processBuilder = new ProcessBuilder();
		$processBuilder->setArguments(array_merge(
			$this->webpackExecutable,
			[
				'--config',
				$config->getConfigPath(),
			],
			$this->webpackArguments
		));
		$processBuilder->setWorkingDirectory($this->workingDirectory);
		$processBuilder->setTimeout(3600);

		$process = $this->buildProcess($processBuilder, [], $this->webpackTtyPrefix);

		// remove manifest file if exists - keep sure we create new one
		if (file_exists($this->manifestJsonPath)) {
			unlink($this->manifestJsonPath);
		}

		$process->mustRun($callback);
		$this->saveManifest();
	}

	public function compileAndWatch(Closure $callback = null, $isDevServer = false) {

		$config = $this->webpackConfigManager->dump();

		$processBuilder = new ProcessBuilder();

		if ($isDevServer) {

			$processBuilder->setArguments(array_merge(
				$this->devServerExecutable,
				[
					'--config',
					$config->getConfigPath(),
				],
				$this->devServerArguments
			));

		} else {

			$processBuilder->setArguments(array_merge(
				$this->webpackExecutable,
				[
					'--config',
					$config->getConfigPath(),
					'--watch'
				]
			));
		}

		$processBuilder->setWorkingDirectory($this->workingDirectory);
		$processBuilder->setTimeout(0);

		$prefix = DIRECTORY_SEPARATOR === '\\' ? [] : ['exec'];
		$ttyPrefix = array_merge($prefix, $this->devServerTtyPrefix);
		$process = $this->buildProcess($processBuilder, $prefix, $ttyPrefix);
		$this->addEnvironment($process, 'WEBPACK_MODE=' . ($isDevServer ? 'server' : 'watch'));

		// remove manifest file if exists - keep sure we create new one
		if (file_exists($this->manifestJsonPath)) {
			$this->logger->info('Deleting manifest file', [$this->manifestJsonPath]);
			unlink($this->manifestJsonPath);
		}

		$that = $this;
		$logger = $this->logger;

		$processCallback = function ($type, $buffer) use ($that, $callback, $logger) {

			$that->saveManifest(false);

			$logger->info('Processing callback from process', [
				$type,
				$buffer,
			]);

			if ($callback !== null) {
				$callback($type, $buffer);
			}
		};

		$this->logger->info('Starting process', [$process->getCommandLine()]);
		$process->start($processCallback);

		try {
			$this->loop($process, $config, $processCallback);
		} catch (Exception $exception) {
			$process->stop();
			throw $exception;
		}
	}

	private function loop(Process $process, WebpackConfig $config, $processCallback) {

		while (true) {

			sleep(1);

			$this->logger->debug('Dumping webpack configuration');

			$config = $this->webpackConfigManager->dump($config);

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
				$this->saveManifest(false);
			}
		}
	}

	public function saveManifest($failIfMissing = true) {

		if (!file_exists($this->manifestJsonPath)) {

			if ($failIfMissing) {

				throw new RuntimeException(
					'Missing manifest file in ' . $this->manifestJsonPath
					. '. Keep sure assets-webpack-plugin is enabled with the same path in webpack config'
				);
			}
			return;
		}

		$manifest = json_decode(file_get_contents($this->manifestJsonPath), true);
		$this->manifestStorage->saveManifest($manifest);

		if (!unlink($this->manifestJsonPath)) {
			throw new RuntimeException('Cannot unlink manifest file at ' . $this->manifestJsonPath);
		}
	}

	private function buildProcess(ProcessBuilder $processBuilder, $prefix, $ttyPrefix) {

		if ($this->disableTty) {
			$processBuilder->setPrefix($prefix);
			return $processBuilder->getProcess();
		}

		// try to set prefix with TTY support
		$processBuilder->setPrefix($ttyPrefix);
		$process = $processBuilder->getProcess();

		try {
			$process->setTty(true);
			$this->addEnvironment($process, 'TTY_MODE=on');
		}
		catch (ProcessRuntimeException $exception) {

			// if TTY is not available, fall back to default prefix if it's different
			if ($prefix !== $ttyPrefix) {
				$processBuilder->setPrefix($prefix);
				$process = $processBuilder->getProcess();
			}
		}

		return $process;
	}

	/**
	 * Modifies process command to add environment variable
	 * Used instead of setEnv because:
	 *  1) currently practically used only in TTY mode, which is only available in Linux
	 *  2) setEnv resets all other environment variables, like PATH - this breaks things
	 *  3) there is no portable way to get all current environment variables, $_ENV is empty by default
	 *
	 * @param Process $process
	 * @param string  $environment
	 */
	private function addEnvironment(Process $process, $environment) {

		if (DIRECTORY_SEPARATOR !== '\\') {
			$process->setCommandLine($environment . ' ' . $process->getCommandLine());
		}
	}
}
