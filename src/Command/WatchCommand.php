<?php

namespace GoldenPlanet\WebpackBundle\Command;

use GoldenPlanet\WebpackBundle\Compiler\WebpackCompiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Runs webpack with `--watch` argument
 */
class WatchCommand extends Command {

	private $compiler;

	/**
	 * WatchCommand constructor.
	 *
	 * @param WebpackCompiler $compiler
	 */
	public function __construct(
		WebpackCompiler $compiler
	) {
		parent::__construct('webpack:watch');

		$this->compiler = $compiler;
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
			);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {

		$this->compiler->compileAndWatch(
			function ($type, $buffer) use ($output) {

				if (Process::ERR === $type) {
					$output->write('<error>' . $buffer . '</error>');
				} else {
					$output->write($buffer);
				}
			}
		);
	}
}
