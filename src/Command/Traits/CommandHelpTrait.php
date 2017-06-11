<?php

namespace GoldenPlanet\WebpackBundle\Command\Traits;

use GoldenPlanet\WebpackBundle\Model\WebpackConfigModel;

trait CommandHelpTrait
{
	/**
	 * Help for webpack:compile command
	 *
	 * @return string
	 */
	private function getCompileCommandHelp()
	{
		return <<<'EOT'
The <info>%command.name%</info> command compiles webpack assets.

    <info>%command.full_name%</info>

Pass the --env=prod flag to compile for production.

    <info>%command.full_name% --env=prod</info>
EOT;
	}

	/**
	 * Help for webpack:dev-server command
	 *
	 * @return string
	 */
	private function getDevServerCommandHelp()
	{
		return <<<'EOT'
The <info>%command.name%</info> command runs webpack-dev-server as a separate process, 
it listens on <info>localhost:8080</info>. 
By default, assets in development environment are pointed to <info>http://localhost:8080/compiled/*</info>.

    <info>%command.full_name%</info>
EOT;
	}

	/**
	 * Help for webpack:setup command
	 *
	 * @return string
	 */
	private function getSetupCommandHelp()
	{
		$webpackConfigFileName = WebpackConfigModel::WEBPACK_SYMFONY_CONFIG_FILE_NAME;

		return <<<EOT
The <info>%command.name%</info> command copies a default <info>$webpackConfigFileName</info> and <info>package.json</info> files 
and runs <info>npm install</info>. 

After executing this command, you should commit the following files to your repository.

    <info>git add package.json $webpackConfigFileName</info>
EOT;
	}

	/**
	 * Help for webpack:watch command
	 *
	 * @return string
	 */
	private function getWatchCommandHelp()
	{
		return <<<'EOT'
The <info>%command.name%</info> command compiles webpack assets and watches for change.

    <info>%command.full_name%</info>

Pass the --env=prod flag to compile for production.

    <info>%command.full_name% --env=prod</info>
EOT;
	}
}
