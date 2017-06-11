<?php

namespace GoldenPlanet\WebpackBundle\Command\Traits;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;

trait SetupCommandQuestionsTrait
{

	private function ask(InputInterface $input, OutputInterface $output, Question $question)
	{
		/** @var QuestionHelper $helper */
		$helper = $this->getHelper('question');

		return $helper->ask($input, $output, $question);
	}

	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @param                 $fileTargetPath
	 *
	 * @return mixed|string
	 */
	private function askShouldCopy(InputInterface $input, OutputInterface $output, $fileTargetPath)
	{
		$question = new ConfirmationQuestion(sprintf(
			'<question>File in %s already exists. Replace?</question> [yN] ',
			$fileTargetPath
		), false);

		return $this->ask($input, $output, $question);
	}

	private function askIfInstallNeeded(InputInterface $input, OutputInterface $output, Process $process)
	{
		$question = new ConfirmationQuestion(sprintf(
			'<question>Should I install node_modules now?</question> (<code>%s</code>) [Yn] ',
			$process->getCommandLine()
		), true);

		if (!$this->ask($input, $output, $question)) {

			$output->writeln(sprintf(
				'Please run <code>%s</code> in root directory before compiling webpack assets',
				$process->getCommandLine()
			));

			return false;
		}

		return true;
	}
}
