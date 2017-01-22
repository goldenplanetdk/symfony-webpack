<?php

namespace GoldenPlanet\WebpackBundle\Command;

use GoldenPlanet\WebpackBundle\Compiler\WebpackCompiler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class WatchCommand extends Command {

	private $compiler;
	private $logger;

	/**
	 * WatchCommand constructor.
	 *
	 * @param WebpackCompiler $compiler
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		WebpackCompiler $compiler,
		LoggerInterface $logger
	) {
		parent::__construct('webpack:watch');

		$this->compiler = $compiler;
		$this->logger = $logger;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function configure() {
		$this
			->setName('webpack:watch')
			->setDescription('Compile and watch webpack assets')
			->setHelp(<<<EOT
The <info>%command.name%</info> command compiles webpack assets and watches for change.

    <info>%command.full_name%</info>

Pass the --env=prod flag to compile for production.

    <info>%command.full_name% --env=prod</info>
EOT
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$logger = $this->logger;

		$this->compiler->compile(function ($type, $buffer) use ($output, $logger) {

			if (Process::ERR === $type) {
				$logger->error($buffer);
				$output->write('<error>' . $buffer . '</error>');
			} else {
				$logger->debug($buffer);
				$output->write($buffer);
			}
		});
	}
}
