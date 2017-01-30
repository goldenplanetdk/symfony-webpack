<?php

namespace GoldenPlanet\WebpackBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;

class SetupCommand extends Command {

	protected $pathToPackage;
	protected $pathToWebpackConfig;
	protected $rootPath;
	protected $configPath;

	/**
	 * SetupCommand constructor.
	 *
	 * @param null|string $pathToPackage
	 * @param             $pathToWebpackConfig
	 * @param             $rootPath
	 * @param             $configPath
	 */
	public function __construct(
		$pathToPackage,
		$pathToWebpackConfig,
		$rootPath,
		$configPath
	) {
		parent::__construct('webpack:setup');

		$this->pathToPackage = $pathToPackage;
		$this->pathToWebpackConfig = $pathToWebpackConfig;
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
The <info>%command.name%</info> command copies a default <info>webpack.config.js</info> and <info>package.json</info> files and runs <info>npm install</info>. 

After executing this command, you should commit the following files to your repository.

    <info>git add package.json app/config/webpack.config.js</info>
EOT
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		/** @var QuestionHelper $helper */
		$helper = $this->getHelper('question');

		$target = $this->rootPath . '/' . basename($this->pathToPackage);

		$question = new ConfirmationQuestion(sprintf(
			'<question>File in %s already exists. Replace?</question> [yN] ',
			$target
		), false);

		if (
			!file_exists($target)
			|| $helper->ask($input, $output, $question)
		) {
			copy($this->pathToPackage, $target);
			$output->writeln(sprintf('Dumped default package to <info>%s</info>', $target));
		} else {
			$output->writeln(sprintf(
				'Please update <info>%s</info> by example in <info>%s</info> manually',
				$target,
				$this->pathToPackage
			));
		}

		$target = $this->configPath . '/' . basename($this->pathToWebpackConfig);

		$question = new ConfirmationQuestion(sprintf(
			'<question>File in %s already exists. Replace?</question> [yN] ',
			$target
		), false);

		if (
			!file_exists($target)
			|| $helper->ask($input, $output, $question)
		) {
			copy($this->pathToWebpackConfig, $target);
			$output->writeln(sprintf('Dumped default webpack config to <info>%s</info>', $target));
		} else {
			$output->writeln(sprintf(
				'Please update <info>%s</info> by example in <info>%s</info> manually',
				$target,
				$this->pathToWebpackConfig
			));
		}

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
