<?php

namespace GoldenPlanet\WebpackBundle\Command;

use GoldenPlanet\WebpackBundle\Command\Traits\CommandHelpTrait;
use GoldenPlanet\WebpackBundle\Webpack\Compiler\WebpackCompiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs webpack with `--watch` argument.
 */
class WatchCommand extends Command
{
	use CommandHelpTrait;

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
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('webpack:watch')
			->setDescription(Description::WATCH)
			->setHelp($this->getWatchCommandHelp())
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->compiler->compileAndWatch($output);
	}
}
