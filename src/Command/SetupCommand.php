<?php

namespace GoldenPlanet\WebpackBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;

/**
 * Copies default package.json, symfony.webpack.config.js files
 * and optionally installs node_modules
 */
class SetupCommand extends Command {

	protected $pathPackageJson;
	protected $pathWebpackConfig;
	protected $pathWebpackConfigRules;
	protected $rootPath;
	protected $configPath;

	/**
	 * SetupCommand constructor.
	 *
	 * @param string $pathPackageJson
	 * @param string $pathWebpackConfig
	 * @param string $pathWebpackConfigRules
	 * @param string $rootPath
	 * @param string $configPath
	 */
	public function __construct(
		$pathPackageJson,
		$pathWebpackConfig,
		$pathWebpackConfigRules,
		$rootPath,
		$configPath
	) {
		parent::__construct('webpack:setup');

		$this->pathPackageJson = $pathPackageJson;
		$this->pathWebpackConfig = $pathWebpackConfig;
		$this->pathWebpackConfigRules = $pathWebpackConfigRules;
		$this->rootPath = realpath($rootPath);
		$this->configPath = realpath($configPath);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function configure() {
		$this
			->setName('webpack:setup')
			->setDescription('Initial setup for gp webpack bundle')
			->setHelp(<<<EOT
The <info>%command.name%</info> command copies a default <info>symfony.webpack.config.js</info> and <info>package.json</info> files and runs <info>npm install</info>. 

After executing this command, you should commit the following files to your repository.

    <info>package.json symfony.webpack.config.js and webpack-rules.js</info>
EOT
			)
		;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {

		$this->copyFile($input, $output, 'npm package.json', $this->pathPackageJson, $this->rootPath);
		$this->copyFile($input, $output, 'webpack configuration', $this->pathWebpackConfig, $this->configPath);
		$this->copyFile($input, $output, 'webpack module rules',  $this->pathWebpackConfigRules, $this->configPath);

		$this->runNpmInstall($input, $output);

		$output->writeln('Run <bg=white;fg=black>webpack:compile</> to compile assets in dev environment');
		$output->writeln('Run <bg=white;fg=black>webpack:watch</> to compile assets and watch for changes');
		$output->writeln('Run <bg=white;fg=black>webpack:dev-server</> in dev environment');
		$output->writeln('Run <bg=white;fg=green>webpack:compile --env=prod</> to compile assets when deploying');
	}

	/**
	 * Copy default configuration files and confirm overwriting files when exists
	 *
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @param string          $fileSourcePath
	 * @param string          $fileTargetDir
	 * @param string          $fileDescription
	 */
	private function copyFile(InputInterface $input, OutputInterface $output, $fileDescription, $fileSourcePath, $fileTargetDir) {

		/** @var QuestionHelper $helper */
		$helper = $this->getHelper('question');

		$fileTargetPath = $fileTargetDir . '/' . basename($fileSourcePath);
		$shouldCopy = true;

		if (file_exists($fileTargetPath)) {

			$question = new ConfirmationQuestion(sprintf(
				'<question>File in %s already exists. Replace?</question> [yN] ',
				$fileTargetPath
			), false);

			$shouldCopy = $helper->ask($input, $output, $question);
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

	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 */
	protected function runNpmInstall(InputInterface $input, OutputInterface $output) {

		/** @var QuestionHelper $helper */
		$helper = $this->getHelper('question');

		$process = new Process('npm install', $this->rootPath);

		$question = new ConfirmationQuestion(
			sprintf('<question>Should I install node dependencies?</question> (%s) [Yn] ', $process->getCommandLine()),
			true
		);

		if ($helper->ask($input, $output, $question)) {

			$process->setTimeout(600);
			$process->run(function ($type, $buffer) use ($output) {
				$output->write($buffer);
			});

		} else {
			$output->writeln('Please update dependencies manually before compiling webpack assets');
		}
	}
}
