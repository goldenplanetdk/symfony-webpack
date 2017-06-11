<?php

class CommonsChunkCest
{
	public function _before(FunctionalTester $I)
	{
		$I->cleanUp();
	}

	public function _after(FunctionalTester $I)
	{
		$I->cleanUp();
	}

	public function getFewDifferentChunksFromGroups(FunctionalTester $I)
	{
		$I->bootKernelWith('commons_chunk');

		$I->runCommand('gp_webpack.command.setup');
		$I->extendSymfonyWebpackConfig('grouped-commons-chunks');

		$I->runCommand('gp_webpack.command.compile');
		$I->seeCommandStatusCode(0);
		$I->seeInCommandDisplay('webpack');
		$I->dontSeeInCommandDisplay('error');

		$this->assertFront($I);
		$this->assertAdmin($I);
	}

	public function getNamedCommonsChunk(FunctionalTester $I)
	{
		$I->bootKernelWith('commons_chunk');

		$I->runCommand('gp_webpack.command.setup');
		$I->extendSymfonyWebpackConfig('named-commons-chunk');

		$I->runCommand('gp_webpack.command.compile');
		$I->seeCommandStatusCode(0);
		$I->seeInCommandDisplay('webpack');
		$I->dontSeeInCommandDisplay('error');

		$I->amOnPage('/commons-chunk/vendor');
		$I->canSeeResponseCodeIs(200);
		$I->dontSee('Manifest file not found');

		$url = $I->grabAttributeFrom('link[rel=stylesheet]#vendor-css', 'href');
		preg_match('#/compiled/(.*)#', $url, $matches);
		$I->seeFileFound(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->openFile(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->canSeeInThisFile('vendor-1');
		$I->canSeeInThisFile('vendor-2');

		$url = $I->grabAttributeFrom('script#vendor-js', 'src');
		preg_match('#/compiled/(.*)#', $url, $matches);
		$I->seeFileFound(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->openFile(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->canSeeInThisFile('vendor-1');
		$I->canSeeInThisFile('vendor-2');
	}

	private function assertFront(FunctionalTester $I)
	{
		$I->amOnPage('/commons-chunk/front-1');
		$I->canSeeResponseCodeIs(200);
		$I->dontSee('Manifest file not found');

		$url = $I->grabAttributeFrom('link[rel=stylesheet]#commons-chunk-css', 'href');
		preg_match('#/compiled/(.*)#', $url, $matches);
		$I->seeFileFound(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->openFile(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->canSeeInThisFile('vendor-1');
		$I->canSeeInThisFile('vendor-2');

		$url = $I->grabAttributeFrom('link[rel=stylesheet]#main-css', 'href');
		preg_match('#/compiled/(.*)#', $url, $matches);
		$I->seeFileFound(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->openFile(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->dontSeeInThisFile('vendor-1');
		$I->dontSeeInThisFile('vendor-2');
		$I->canSeeInThisFile('main-1');

		$url = $I->grabAttributeFrom('script#commons-chunk-js', 'src');
		preg_match('#/compiled/(.*)#', $url, $matches);
		$I->seeFileFound(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->openFile(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->canSeeInThisFile('vendor-1');
		$I->canSeeInThisFile('vendor-2');

		$url = $I->grabAttributeFrom('script#main-js', 'src');
		preg_match('#/compiled/(.*)#', $url, $matches);
		$I->seeFileFound(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->openFile(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->dontSeeInThisFile('vendor-1');
		$I->dontSeeInThisFile('vendor-2');
		$I->canSeeInThisFile('main-1');
	}

	private function assertAdmin(FunctionalTester $I)
	{
		$I->amOnPage('/commons-chunk/admin-1');
		$I->canSeeResponseCodeIs(200);
		$I->dontSee('Manifest file not found');

		$url = $I->grabAttributeFrom('link[rel=stylesheet]#commons-chunk-css', 'href');
		preg_match('#/compiled/(.*)#', $url, $matches);
		$I->seeFileFound(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->openFile(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->canSeeInThisFile('vendor-1');

		$url = $I->grabAttributeFrom('link[rel=stylesheet]#main-css', 'href');
		preg_match('#/compiled/(.*)#', $url, $matches);
		$I->seeFileFound(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->openFile(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->dontSeeInThisFile('vendor-1');
		$I->canSeeInThisFile('main-1');

		$url = $I->grabAttributeFrom('script#commons-chunk-js', 'src');
		preg_match('#/compiled/(.*)#', $url, $matches);
		$I->seeFileFound(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->openFile(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->canSeeInThisFile('vendor-1');

		$url = $I->grabAttributeFrom('script#main-js', 'src');
		preg_match('#/compiled/(.*)#', $url, $matches);
		$I->seeFileFound(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->openFile(__DIR__ . '/Fixtures/web/compiled/' . $matches[1]);
		$I->dontSeeInThisFile('vendor-1');
		$I->canSeeInThisFile('main-1');
	}
}
