<?php

namespace GoldenPlanet\WebpackBundle\Command;

use GoldenPlanet\WebpackBundle\Command\Traits\CommandHelpTrait;
use GoldenPlanet\WebpackBundle\Webpack\Compiler\WebpackCompiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs webpack
 */
class CompileCommand extends Command
{
	use CommandHelpTrait;

	private $compiler;

	/**
	 * CompileCommand constructor.
	 *
	 * @param WebpackCompiler $compiler
	 */
	public function __construct(
		WebpackCompiler $compiler
	) {
		parent::__construct('webpack:compile');

		$this->compiler = $compiler;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('webpack:compile')
			->setDescription(Description::COMPILE)
			->setHelp($this->getCompileCommandHelp())
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->compiler->compile($output);
	}
}
