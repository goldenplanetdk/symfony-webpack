<?php

class TagsCest
{
	public function _before(FunctionalTester $I)
	{
		$I->cleanUp();
	}

	public function _after(FunctionalTester $I)
	{
		$I->cleanUp();
	}

	public function getNoErrorIfAssetsAreDumped(FunctionalTester $I)
	{
		$I->bootKernelWith('tags');

		$I->runCommand('gp_webpack.command.setup');
		$I->runCommand('gp_webpack.command.compile');
		$I->seeCommandStatusCode(0);
		$I->seeInCommandDisplay('webpack');
		$I->dontSeeInCommandDisplay('error');

		$I->amOnPage('/tags');
		$I->canSeeResponseCodeIs(200);
		$I->dontSee('Manifest file not found');

		$I->canSeeNumberOfElements('link[rel=stylesheet]', 3);
		$urlList = $I->grabMultiple('link', 'href');
		foreach ($urlList as $i => $url) {
			preg_match('#/compiled/(.*)#', $url, $matches);
			$I->seeFileFound(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
			$I->openFile(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
			$I->canSeeInThisFile('.class' . $i . ' {');
		}

		$I->canSeeNumberOfElements('script', 2);
		$urlList = $I->grabMultiple('script', 'src');
		foreach ($urlList as $i => $url) {
			preg_match('#/compiled/(.*)#', $url, $matches);
			$I->seeFileFound(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
			$I->openFile(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
			$I->canSeeInThisFile('function f' . $i . '() {');
		}

		$I->canSeeNumberOfElements('img', 2);
		$urlList = $I->grabMultiple('img', 'src');
		foreach ($urlList as $i => $url) {
			preg_match('#/compiled/(.*)#', $url, $matches);
			$I->seeFileFound(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		}
	}
}
