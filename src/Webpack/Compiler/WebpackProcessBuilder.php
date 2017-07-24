<?php

namespace GoldenPlanet\WebpackBundle\Webpack\Compiler;

use GoldenPlanet\WebpackBundle\Model\WebpackConfigModel;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Exception\RuntimeException as ProcessRuntimeException;
use Symfony\Component\Process\ProcessUtils;

class WebpackProcessBuilder
{
	const DASHBOARD_MODE_ENABLED_ALWAYS = 'enabled_always';
	const DASHBOARD_MODE_ENABLED_ON_DEV_SERVER = 'enabled_on_dev_server';
	const DASHBOARD_MODE_DISABLED = 'disabled';

	private $workingDirectory;
	private $disableTty;
	private $webpackExecutable;
	private $webpackArguments;
	private $devServerExecutable;
	private $devServerArguments;
	private $dashboardExecutable;
	private $dashboardMode;

	public function __construct(
		$workingDirectory,
		$disableTty,
		array $webpackExecutable,
		array $webpackArguments,
		array $devServerExecutable,
		array $devServerArguments,
		array $dashboardExecutable,
		$dashboardMode
	) {
		$this->workingDirectory = $workingDirectory;
		$this->disableTty = $disableTty;
		$this->webpackExecutable = $webpackExecutable;
		$this->webpackArguments = $webpackArguments;
		$this->devServerExecutable = $devServerExecutable;
		$this->devServerArguments = $devServerArguments;
		$this->dashboardExecutable = $dashboardExecutable;
		$this->dashboardMode = $dashboardMode;
	}

	/**
	 * @param WebpackConfigModel $config
	 *
	 * @return Process
	 */
	public function buildWebpackProcess(WebpackConfigModel $config)
	{
		$processBuilder = new ProcessBuilder();

		$configPathArgument = [
			'--config',
			$config->getConfigPath(),
			'--color',
		];

		$processArgumentsArray = array_merge(
			$this->webpackExecutable,
			$configPathArgument,
			$this->webpackArguments
		);

		$processBuilder->setArguments($processArgumentsArray);
		$processBuilder->setTimeout(3600);

		$process = $this->buildProcess($processBuilder);

		if ($this->dashboardMode === self::DASHBOARD_MODE_ENABLED_ALWAYS) {
			$this->addDashboard($process);
		}

		return $process;
	}

	/**
	 * @param WebpackConfigModel $config
	 * @param boolean            $isDevServer
	 *
	 * @return Process
	 */
	public function buildDevServerProcess(WebpackConfigModel $config, $isDevServer)
	{
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
					'--watch',
				]
			));
		}

		$processBuilder->setTimeout(0);
		$processBuilder->setEnv('WEBPACK_MODE', $isDevServer ? 'server' : 'watch');

		$process = $this->buildProcess($processBuilder);

		$dashboardEnabled = in_array($this->dashboardMode, [
			self::DASHBOARD_MODE_ENABLED_ALWAYS,
			self::DASHBOARD_MODE_ENABLED_ON_DEV_SERVER,
		], true);

		if ($dashboardEnabled) {
			$this->addDashboard($process);
		}

		// from symfony 3.3 exec is added automatically
		if (DIRECTORY_SEPARATOR !== '\\' && substr($process->getCommandLine(), 0, 5) !== 'exec ') {
			$process->setCommandLine('exec ' . $process->getCommandLine());
		}

		return $process;
	}

	private function buildProcess(ProcessBuilder $processBuilder)
	{
		$processBuilder->setWorkingDirectory($this->workingDirectory);

		$process = $processBuilder->getProcess();
		if ($this->disableTty) {
			return $process;
		}

		try {
			$process->setTty(true);
		} catch (ProcessRuntimeException $exception) {
			// thrown if TTY is not available - just ignore
		}

		return $process;
	}

	private function addDashboard(Process $process)
	{
		if (!$process->isTty()) {
			return;
		}

		$prefix = implode(' ', array_map(function ($part) {
			return ProcessUtils::escapeArgument($part);
		}, $this->dashboardExecutable));

		$commandLine = $process->getCommandLine();
		if (substr($commandLine, 0, 5) === 'exec ') {
			$commandLine = substr($commandLine, 5);
		}
		$process->setCommandLine($prefix . ' -- ' . $commandLine);

		$process->setEnv(['WEBPACK_DASHBOARD' => 'enabled'] + $process->getEnv());
	}
}
