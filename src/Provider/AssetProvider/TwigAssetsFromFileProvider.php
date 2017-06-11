<?php

namespace GoldenPlanet\WebpackBundle\Provider\AssetProvider;

use GoldenPlanet\WebpackBundle\Model\AssetItemModel;
use GoldenPlanet\WebpackBundle\Model\AssetCollectionModel;
use GoldenPlanet\WebpackBundle\ErrorHandler\ErrorHandlerInterface;
use GoldenPlanet\WebpackBundle\Exception\InvalidContextException;
use GoldenPlanet\WebpackBundle\Exception\InvalidResourceException;
use GoldenPlanet\WebpackBundle\Exception\ResourceParsingException;
use GoldenPlanet\WebpackBundle\Twig\WebpackExtension;
use Twig_Environment;
use Twig_Error_Syntax;
use Twig_Node;
use Twig_Node_Expression_Constant;
use Twig_Node_Expression_Function;
use Twig_Source;

class TwigAssetsFromFileProvider implements AssetsFromFileProviderInterface
{
	private $twig;
	private $errorHandler;

	public function __construct(
		Twig_Environment $twig,
		ErrorHandlerInterface $errorHandler
	) {
		$this->twig = $twig;
		$this->errorHandler = $errorHandler;
	}

	/**
	 * Tokenize and parse a twig file to find all entry points
	 * and files for commons chunk(s)
	 *
	 * @param string     $filePath
	 * @param array|null $previousContext Former assets collection data
	 *
	 * @return AssetCollectionModel
	 */
	public function getAssets($filePath, $previousContext = null)
	{
		$this->validateFile($filePath);

		if ($previousContext !== null) {

			if (
				!is_array($previousContext)
				|| !isset($previousContext['modified_at'])
				|| !is_int($previousContext['modified_at'])
				|| !isset($previousContext['assets'])
				|| !is_array($previousContext['assets'])
			) {
				throw new InvalidContextException(
					'Expected context with int `modified_at` and array `assets`',
					$previousContext
				);
			}

			if ($previousContext['modified_at'] === filemtime($filePath)) {
				$assetCollection = new AssetCollectionModel();
				$assetCollection->setAssets($previousContext['assets']);
				$assetCollection->setContext($previousContext);

				return $assetCollection;
			}
		}

		try {
			$tokens = $this->twig->tokenize(new Twig_Source(file_get_contents($filePath), $filePath));
			$node = $this->twig->parse($tokens);

		} catch (Twig_Error_Syntax $exception) {

			$this->errorHandler->processException(
				new ResourceParsingException('Got twig syntax exception while parsing', 0, $exception)
			);

			return new AssetCollectionModel();
		}

		$assets = $this->loadNode($node, $filePath);

		$assetCollection = new AssetCollectionModel();
		$assetCollection->setAssets($assets);
		$assetCollection->setContext([
			'modified_at' => filemtime($filePath),
			'assets' => $assets,
		]);

		return $assetCollection;
	}

	/**
	 * @param Twig_Node $node
	 * @param string    $filePath
	 *
	 * @return array
	 */
	private function loadNode(Twig_Node $node, $filePath)
	{
		if ($this->isFunctionNode($node)) {
			/* @var Twig_Node_Expression_Function $node */
			return $this->parseFunctionNode($node, sprintf('File %s, line %s', $filePath, $node->getTemplateLine()));
		}

		$assets = [];

		foreach ($node as $child) {

			if ($child instanceof Twig_Node) {
				$assets = array_merge($assets, $this->loadNode($child, $filePath));
			}
		}

		return $assets;
	}

	private function isFunctionNode(Twig_Node $node)
	{
		if ($node instanceof Twig_Node_Expression_Function) {
			return $node->getAttribute('name') === WebpackExtension::FUNCTION_NAME;
		}

		return false;
	}

	/**
	 * @param Twig_Node_Expression_Function $functionNode
	 * @param string                        $contextHint
	 *
	 * @return array
	 */
	private function parseFunctionNode(Twig_Node_Expression_Function $functionNode, $contextHint)
	{
		$arguments = iterator_to_array($functionNode->getNode('arguments'));

		if (!is_array($arguments)) {
			throw new ResourceParsingException('arguments is not an array');
		}

		if (count($arguments) < 1 || count($arguments) > 3) {
			throw new ResourceParsingException(sprintf(
				'Expected one to three arguments passed to function %s. %s',
				WebpackExtension::FUNCTION_NAME,
				$contextHint
			));
		}

		$asset = new AssetItemModel();

		$resourceArgument = isset($arguments[0]) ? $arguments[0] : $arguments['resource'];
		$asset->setResource($this->getArgumentValue($resourceArgument, $contextHint));

		$groupArgument = null;

		if (isset($arguments[2])) {
			$groupArgument = $arguments[2];
		} elseif (isset($arguments['group'])) {
			$groupArgument = $arguments['group'];
		}

		if ($groupArgument !== null) {
			$asset->setGroup($this->getArgumentValue($groupArgument, $contextHint));
		}

		return [$asset];
	}

	/**
	 * @param Twig_Node $argument
	 * @param string    $contextHint
	 *
	 * @return mixed
	 */
	private function getArgumentValue(Twig_Node $argument, $contextHint)
	{
		if (!$argument instanceof Twig_Node_Expression_Constant) {

			throw new ResourceParsingException(sprintf(
				'Argument passed to function %s must be text node to parse without context. %s',
				WebpackExtension::FUNCTION_NAME,
				$contextHint
			));
		}

		return $argument->getAttribute('value');
	}

	private function validateFile($fileName)
	{
		if (!is_string($fileName)) {
			throw new InvalidResourceException('Expected string filename as resource', $fileName);
		} elseif (!is_file($fileName)) {
			throw new InvalidResourceException('File not found', $fileName);
		} elseif (!is_readable($fileName)) {
			throw new InvalidResourceException('File is not readable', $fileName);
		} elseif (!stream_is_local($fileName)) {
			throw new InvalidResourceException('File is not local', $fileName);
		}
	}
}
