<?php

namespace GoldenPlanet\WebpackBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;

/**
 * Copies default package.json and symfony.webpack.config.js
 * and optionally installs node_modules
 */
class SetupCommand extends Command {

	protected $pathPackageJson;
	protected $pathWebpackConfig;
	protected $rootPath;
	protected $configPath;

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
	 * {@inheritDoc}
	 */
	protected function configure() {
		$this
			->setName('webpack:setup')
			->setDescription('Initial setup for gp webpack bundle')
			->setHelp(<<<EOT
The <info>%command.name%</info> command copies a default <info>symfony.webpack.config.js</info> and <info>package.json</info> files and runs <info>npm install</info>. 

After executing this command, you should commit the following files to your repository.

    <info>git add package.json app/config/symfony.webpack.config.js</info>
EOT
			);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {

		/** @var QuestionHelper $helper */
		$helper = $this->getHelper('question');

		/**
		 * Copy package.json
		 */
		$fileTargetPackageJson = $this->rootPath . '/' . basename($this->pathPackageJson);
		$shouldCopyPackageJson = true;

		if (file_exists($fileTargetPackageJson)) {

			$question = new ConfirmationQuestion(sprintf(
				'<question>File in %s already exists. Replace?</question> [yN] ',
				$fileTargetPackageJson
			), false);

			$shouldCopyPackageJson = $helper->ask($input, $output, $question);
		}

		if ($shouldCopyPackageJson) {

			copy($this->pathPackageJson, $fileTargetPackageJson);
			$output->writeln(sprintf('Dumped default package to <info>%s</info>', $fileTargetPackageJson));

		} else {

			$output->writeln(sprintf(
				'Please update <info>%s</info> by example in <info>%s</info> manually',
				$fileTargetPackageJson,
				$this->pathPackageJson
			));
		}

		/**
		 * Copy symfony.webpack.config.js
		 */
		$fileTargetWebpackConfig = $this->configPath . '/' . basename($this->pathWebpackConfig);

		$question = new ConfirmationQuestion(sprintf(
			'<question>File in %s already exists. Replace?</question> [yN] ',
			$fileTargetWebpackConfig
		), false);

		if (
			!file_exists($fileTargetWebpackConfig)
			|| $helper->ask($input, $output, $question)
		) {
			copy($this->pathWebpackConfig, $fileTargetWebpackConfig);
			$output->writeln(sprintf('Dumped default webpack config to <info>%s</info>', $fileTargetWebpackConfig));
		} else {
			$output->writeln(sprintf(
				'Please update <info>%s</info> by example in <info>%s</info> manually',
				$fileTargetWebpackConfig,
				$this->pathWebpackConfig
			));
		}

		/**
		 * Install node_modules
		 */
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

		$output->writeln('Run <bg=white;fg=black>webpack:compile</> to compile assets in dev environment');
		$output->writeln('Run <bg=white;fg=black>webpack:watch</> to compile assets and watch for changes');
		$output->writeln('Run <bg=white;fg=black>webpack:dev-server</> in dev environment');
		$output->writeln('Run <bg=white;fg=green>webpack:compile --env=prod</> to compile assets when deploying');
	}
}
