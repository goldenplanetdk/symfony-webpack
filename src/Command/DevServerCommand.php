<?php

namespace GoldenPlanet\WebpackBundle\Command;

use GoldenPlanet\WebpackBundle\Compiler\WebpackCompiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DevServerCommand extends Command {

	private $compiler;

	/**
	 * DevServerCommand constructor.
	 *
	 * @param WebpackCompiler $compiler
	 */
	public function __construct(
		WebpackCompiler $compiler
	) {
		parent::__construct('webpack:dev-server');

		$this->compiler = $compiler;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function configure() {
		$this
			->setName('webpack:dev-server')
			->setDescription('Run a webpack-dev-server as a separate process on localhost:8080')
			->setHelp(<<<EOT
The <info>%command.name%</info> command runs webpack-dev-server as a separate process, it listens on <info>localhost:8080</info>. By default, assets in development environment are pointed to <info>http://localhost:8080/compiled/*</info>.

    <info>%command.full_name%</info>
EOT
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$this->compiler->compileAndWatch(
			function ($type, $buffer) use ($output) {

				if (Process::ERR === $type) {
					$output->write('<error>' . $buffer . '</error>');
				} else {
					$output->write($buffer);
				}
			},
			true
		);
	}
}
