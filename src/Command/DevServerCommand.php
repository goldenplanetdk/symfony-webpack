<?php

namespace GoldenPlanet\WebpackBundle\Command;

use GoldenPlanet\WebpackBundle\Command\Traits\CommandHelpTrait;
use GoldenPlanet\WebpackBundle\Webpack\Compiler\WebpackCompiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs webpack-dev-server.
 */
class DevServerCommand extends Command
{
	use CommandHelpTrait;

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
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('webpack:dev-server')
			->setDescription(Description::DEV_SERVER)
			->setHelp($this->getDevServerCommandHelp())
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->compiler->compileAndWatch($output, true);
	}
}
