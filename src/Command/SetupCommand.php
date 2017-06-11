<?php

namespace GoldenPlanet\WebpackBundle\Command;

use GoldenPlanet\WebpackBundle\Command\Traits\CommandHelpTrait;
use GoldenPlanet\WebpackBundle\Command\Traits\SetupCommandMessagesTrait;
use GoldenPlanet\WebpackBundle\Command\Traits\SetupCommandQuestionsTrait;
use GoldenPlanet\WebpackBundle\Model\WebpackConfigModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Copies default package.json, webpack.symfony.config.js files
 * and optionally installs node_modules.
 */
class SetupCommand extends Command
{
	use CommandHelpTrait;
	use SetupCommandMessagesTrait;
	use SetupCommandQuestionsTrait;

	private $pathPackageJson;
	private $pathWebpackConfig;
	private $rootPath;
	private $configPath;

	/**
	 * SetupCommand constructor.
	 *
	 * @param string $pathPackageJson
	 * @param string $pathWebpackConfig
	 * @param string $rootPath
	 * @param string $configPath
	 */
	public function __construct(
		$pathPackageJson,
		$pathWebpackConfig,
		$rootPath,
		$configPath
	) {
		parent::__construct('webpack:setup');

		$this->pathPackageJson = $pathPackageJson;
		$this->pathWebpackConfig = $pathWebpackConfig;
		$this->rootPath = realpath($rootPath);
		$this->configPath = realpath($configPath);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('webpack:setup')
			->setDescription(Description::SETUP)
			->setHelp($this->getSetupCommandHelp())
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->addStylesConfiguration($output);

		$this->copyFile($input, $output, 'NPM <code>package.json</code>', $this->pathPackageJson, $this->rootPath);
		$this->copyFile($input, $output, 'Webpack <code>config.js</code>', $this->pathWebpackConfig, $this->configPath, WebpackConfigModel::WEBPACK_SYMFONY_CONFIG_FILE_NAME);

		$this->installNodeModules($input, $output);

		$this->outputWebpackConsoleCommands($output);
	}

	/**
	 * Copy default configuration files and confirm overwriting files when exists.
	 *
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @param string          $fileDescription
	 * @param string          $fileSourcePath
	 * @param string          $fileTargetDir
	 * @param string          $fileName
	 */
	private function copyFile(InputInterface $input, OutputInterface $output, $fileDescription, $fileSourcePath, $fileTargetDir, $fileName = '')
	{
		$fileTargetPath = $fileTargetDir . '/' . ($fileName ?: basename($fileSourcePath));

		$shouldCopy = true;

		if (file_exists($fileTargetPath)) {
			$shouldCopy = $this->askShouldCopy($input, $output, $fileTargetPath);
		}

		if ($shouldCopy) {
			copy($fileSourcePath, $fileTargetPath);
			$outputMessage = sprintf("Dumped default $fileDescription to <info>%s</info>", $fileTargetPath);

		} else {

			$outputMessage = sprintf(
				'Please update <info>%s</info> by looking at example in <info>%s</info> manually',
				$fileTargetPath,
				$fileSourcePath
			);
		}

		$output->writeln($outputMessage);
	}

	private function installNodeModules(InputInterface $input, OutputInterface $output)
	{
		$process = new Process('yarn --version');
		$yarnInstalled = $process->run() === 0;
		$process = new Process('npm --version');
		$npmInstalled = $process->run() === 0;

		if (!$yarnInstalled && !$npmInstalled) {
			$this->outputDependenciesError($output);
			return;
		}

		$process = new Process($yarnInstalled ? 'yarn' : 'npm install', $this->rootPath);

		if (!$this->askIfInstallNeeded($input, $output, $process)) {
			return;
		}

		$this->runProcess($process, $output);

		$filesForGit = [
			'package.json',
			'app/config/' . WebpackConfigModel::WEBPACK_SYMFONY_CONFIG_FILE_NAME,
		];

		if ($yarnInstalled) {
			$filesForGit[] = 'yarn.lock';
		}

		$this->outputAdditionalActions($output, $filesForGit);
	}

	private function runProcess(Process $process, OutputInterface $output)
	{
		$process->setTimeout(600);

		$process->run(function ($type, $buffer) use ($output) {
			$output->write($buffer);
		});

		if (!$process->isSuccessful()) {
			$this->showProcessError($process, $output);
		}
	}

}
