<?php

namespace GoldenPlanet\WebpackBundle\Twig;

use Twig_Error_Syntax as SyntaxError;
use Twig_Node as Node;
use Twig_Node_Expression_AssignName as AssignNameExpression;
use Twig_Node_Expression_Constant as ConstantExpression;
use Twig_Node_Expression_Function as FunctionExpression;
use Twig_Node_If as IfNode;
use Twig_Node_Set as SetNode;
use Twig_Token as Token;
use Twig_TokenParser as TokenParser;

class WebpackTokenParser extends TokenParser {

	private $tag;
	private $functionName;
	private $assetType;

	/**
	 * @param string $tag          tag name, for example webpack_stylesheets. End tag is same with "end_" prefix
	 * @param string $functionName function name to call to get asset, usually webpack_asset
	 * @param string $assetType    type of asset - second argument to pass to webpack_asset. For example "css"
	 */
	public function __construct($tag, $functionName, $assetType) {
		$this->tag = $tag;
		$this->functionName = $functionName;
		$this->assetType = $assetType;
	}

	public function parse(Token $token) {
		$inputs = [];

		$stream = $this->parser->getStream();
		while (!$stream->test(Token::BLOCK_END_TYPE)) {
			if ($stream->test(Token::STRING_TYPE)) {
				$inputs[] = $stream->next()->getValue();
			} else {
				$token = $stream->getCurrent();
				throw new SyntaxError(sprintf(
					'Unexpected token "%s" of value "%s"',
					Token::typeToEnglish($token->getType()),
					$token->getValue()
				), $token->getLine(), $stream->getSourceContext());
			}
		}

		$stream->expect(Token::BLOCK_END_TYPE);

		$endTag = 'end_' . $this->getTag();
		$body = $this->parser->subparse(function (Token $token) use ($endTag) {
			return $token->test([$endTag]);
		}, true);
		$stream->expect(Token::BLOCK_END_TYPE);

		$nodes = [];
		foreach ($inputs as $input) {
			$nodes[] = $this->createNodeForInput($input, $body, $token->getLine());
		}
		return new Node($nodes);
	}

	public function getTag() {
		return $this->tag;
	}

	private function createNodeForInput($input, $body, $lineNo) {
		// webpack_asset('path/asset.css', 'css')
		$valueExpression = new FunctionExpression(
			$this->functionName,
			new Node([
				new ConstantExpression($input, $lineNo),
				new ConstantExpression($this->assetType, $lineNo),
			]),
			$lineNo
		);

		// set asset_url = webpack_asset('path/asset.css', 'css')
		$assignExpression = new SetNode(
			false,
			new AssignNameExpression('asset_url', $lineNo),
			$valueExpression,
			$lineNo,
			$this->getTag()
		);

		// if (asset_url) { ... }
		$ifBlock = new IfNode(new Node([
			new AssignNameExpression('asset_url', $lineNo),
			$body,
		]), null, $lineNo, $this->getTag());

		return new Node([
			$assignExpression,
			$ifBlock,
		]);
	}
}
