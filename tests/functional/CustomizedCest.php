<?php

class CustomizedCest
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
		$I->bootKernelWith('customized');

		$I->runCommand('gp_webpack.command.setup');
		$I->runCommand('gp_webpack.command.compile');
		$I->seeCommandStatusCode(0);
		$I->seeInCommandDisplay('webpack');
		$I->dontSeeInCommandDisplay('error');

		$I->amOnPage('/customized');
		$I->canSeeResponseCodeIs(200);
		$I->dontSee('Manifest file not found');

		$I->seeInSource('<link rel="stylesheet" id="css1" href="/assets/');
		$href = $I->grabAttributeFrom('link#css1', 'href');
		preg_match('#/assets/(.*)#', $href, $matches);
		$I->seeFileFound(__DIR__ . '/Fixtures/web/assets/' . $matches[1]);
		$I->openFile(__DIR__ . '/Fixtures/web/assets/' . $matches[1]);
		$I->canSeeInThisFile('.green');
		$I->canSeeInThisFile('.red');
		$I->amGoingTo('Check if less file was compiled');
		$I->canSeeInThisFile('color: #123456');
		$I->amGoingTo('Check if sass file was compiled');
		$I->canSeeInThisFile('color: #654321');
		$I->amGoingTo('Check if cat.png was included');
		$I->canSeeInThisFile('background: url(/assets/');

		$I->seeInSource('<link rel="stylesheet" id="css2" href="/assets/');
		$href = $I->grabAttributeFrom('link#css2', 'href');
		preg_match('#/assets/(.*)#', $href, $matches);
		$I->seeFileFound(__DIR__ . '/Fixtures/web/assets/' . $matches[1]);
		$I->openFile(__DIR__ . '/Fixtures/web/assets/' . $matches[1]);
		$I->canSeeInThisFile('color: #123456');

		$I->seeInSource('<script src="/assets/');
		$src = $I->grabAttributeFrom('script', 'src');
		preg_match('#/assets/(.*)#', $src, $matches);
		$I->seeFileFound(__DIR__ . '/Fixtures/web/assets/' . $matches[1]);
		$I->openFile(__DIR__ . '/Fixtures/web/assets/' . $matches[1]);
		$I->canSeeInThisFile('additional-asset-content');
		$I->canSeeInThisFile('additional asset B');
		$I->canSeeInThisFile('app-asset-content');
		$I->dontSeeInThisFile('featureA-content');
		$I->dontSeeInThisFile('featureB-content');

		$I->seeInSource('<img src="/assets/');
		$src = $I->grabAttributeFrom('img', 'src');
		preg_match('#/assets/(.*)#', $src, $matches);
		$I->seeFileFound(__DIR__ . '/Fixtures/web/assets/' . $matches[1]);
	}
}
