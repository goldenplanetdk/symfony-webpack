<?php

namespace GoldenPlanet\WebpackBundle\AssetProvider;

use GoldenPlanet\WebpackBundle\ErrorHandler\ErrorHandlerInterface;
use GoldenPlanet\WebpackBundle\Exception\InvalidContextException;
use GoldenPlanet\WebpackBundle\Exception\InvalidResourceException;
use GoldenPlanet\WebpackBundle\Exception\ResourceParsingException;
use GoldenPlanet\WebpackBundle\Twig\WebpackExtension;
use Twig_Environment as Environment;
use Twig_Error_Syntax as SyntaxException;
use Twig_Node as Node;
use Twig_Node_Expression_Constant as ConstantFunction;
use Twig_Node_Expression_Function as ExpressionFunction;
use Twig_Source as Source;

class TwigAssetProvider implements AssetProviderInterface {

	private $twig;
	private $errorHandler;

	public function __construct(
		Environment $twig,
		ErrorHandlerInterface $errorHandler
	) {
		$this->twig = $twig;
		$this->errorHandler = $errorHandler;
	}

	public function getAssets($resource, $previousContext = null) {
		if (!is_string($resource)) {
			throw new InvalidResourceException('Expected string filename as resource', $resource);
		} elseif (!is_file($resource) || !is_readable($resource) || !stream_is_local($resource)) {
			throw new InvalidResourceException('File not found, not readable or not local', $resource);
		}

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

			if ($previousContext['modified_at'] === filemtime($resource)) {
				$assetResult = new AssetResult();
				$assetResult->setAssets($previousContext['assets']);
				$assetResult->setContext($previousContext);
				return $assetResult;
			}
		}

		try {
			$tokens = $this->twig->tokenize(new Source(file_get_contents($resource), $resource));
			$node = $this->twig->parse($tokens);
		} catch (SyntaxException $exception) {
			$this->errorHandler->processException(
				new ResourceParsingException('Got twig syntax exception while parsing', 0, $exception)
			);
			return new AssetResult();
		}

		$assets = $this->loadNode($node, $resource);

		$assetResult = new AssetResult();
		$assetResult->setAssets($assets);
		$assetResult->setContext([
			'modified_at' => filemtime($resource),
			'assets'      => $assets,
		]);
		return $assetResult;
	}

	private function loadNode(Node $node, $resource) {

		$assets = [];
		$functionName = WebpackExtension::FUNCTION_NAME_ASSET;

		if ($node instanceof ExpressionFunction) {

			$name = $node->getAttribute('name');

			if ($name === $functionName) {

				$arguments = iterator_to_array($node->getNode('arguments'));

				if (!is_array($arguments)) {
					throw new ResourceParsingException('arguments is not an array');
				}

				if (count($arguments) !== 1 && count($arguments) !== 2) {

					throw new ResourceParsingException(sprintf(
						'Expected exactly one or two arguments passed to function %s in %s at line %s',
						$functionName,
						$resource,
						$node->getTemplateLine()
					));
				}

				if (!$arguments[0] instanceof ConstantFunction) {

					throw new ResourceParsingException(sprintf(
						'Argument passed to function %s must be text node to parse without context. File %s, line %s',
						$functionName,
						$resource,
						$node->getTemplateLine()
					));
				}

				$assets[] = $arguments[0]->getAttribute('value');

				return $assets;
			}
		}

		foreach ($node as $child) {

			if ($child instanceof Node) {

				$assets = array_merge($assets, $this->loadNode($child, $resource));
			}
		}

		return $assets;
	}
}
