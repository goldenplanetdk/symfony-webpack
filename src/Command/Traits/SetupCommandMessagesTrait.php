<?php

namespace GoldenPlanet\WebpackBundle\Command\Traits;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

trait SetupCommandMessagesTrait
{

	private function addStylesConfiguration(OutputInterface $output)
	{
		$output->getFormatter()->setStyle('code', new OutputFormatterStyle('white', 'black', ['bold']));
		$output->getFormatter()->setStyle('bold', new OutputFormatterStyle(null, null, ['bold']));
	}

	/**
	 * Show error notification when a Node package manager (NPM or Yarn) is not available
	 *
	 * @param OutputInterface $output
	 */
	private function outputDependenciesError(OutputInterface $output)
	{
		$output->writeln(<<<'NOTICE'
            
<error>Dependencies needed</error>
Neither <bold>yarn</bold> nor <bold>npm</bold> was found on the system.
See https://npmjs.com/ or https://yarnpkg.com/ for more information.
You can re-run this command after installing or run <code>yarn install</code> in root directory.

NOTICE
		);
	}

	/**
	 * Show available webpack-bundle-related console commands help
	 *
	 * @param OutputInterface $output
	 */
	private function outputWebpackConsoleCommands(OutputInterface $output)
	{
		$output->writeln(<<<'NOTICE'
            
Run <code>webpack:compile</code> to compile assets in dev environment
Run <code>webpack:watch</code> to compile assets and watch for changes
Run <code>webpack:dev-server</code> in dev environment
Run <code>webpack:compile --env=prod</code> to compile assets when deploying

NOTICE
		);
	}

	/**
	 * Additional action hints
	 * - add files to git (webpack.symfony.config.js, package.json, yarn.lock)
	 * - add node_modules to .gitignore
	 *
	 * @param OutputInterface $output
	 * @param array           $filesForGit
	 */
	private function outputAdditionalActions(OutputInterface $output, array $filesForGit)
	{
		$notice = <<<'NOTICE'
        
<bold>Additional actions needed</bold>

Now you may add the following into your git repository:
<code>git add %s</code>

You may also add <code>node_modules</code> directory into <code>.gitignore</code>:
<code>echo "node_modules" >> .gitignore</code>

Run <code>webpack:compile</code> to compile assets when deploying.

Run <code>webpack:watch</code> or <code>webpack:dev-server</code> in dev environment.

NOTICE;

		$output->writeln(sprintf(
			$notice,
			implode(' ', $filesForGit)
		));
	}

	/**
	 * Show error when process was not executed successfully
	 *
	 * @param Process         $process
	 * @param OutputInterface $output
	 */
	private function showProcessError(Process $process, OutputInterface $output)
	{
		$error = <<<'ERROR'
            
<error>Error running %s (exit code %s)! Please look at the log for errors and re-run command.</error>

ERROR;

		$output->writeln(sprintf(
			$error,
			$process->getCommandLine(),
			$process->getExitCode()
		));
	}
}
