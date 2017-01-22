<?php

namespace GoldenPlanet\WebpackBundle\AssetProvider\CollectionResource;

use GoldenPlanet\WebpackBundle\Exception\InvalidResourceException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class DirectoryResource implements CollectionResourceInterface {

	protected $pattern;

	/**
	 * @param string $pattern file pattern, for example "*.twig"
	 */
	public function __construct($pattern) {
		$this->pattern = $pattern;
	}

	public function getInternalResources($resource) {
		if (!is_string($resource)) {
			throw new InvalidResourceException('Expected string as resource', $resource);
		}

		$resources = [];
		foreach ($this->createFinder($resource) as $file) {
			$resources[] = $file->getRealPath();
		}

		return $resources;
	}

	/**
	 * @param string $resource
	 *
	 * @return Finder|SplFileInfo[]
	 */
	protected function createFinder($resource) {
		$finder = new Finder();
		$finder->in($resource)->followLinks()->files()->name($this->pattern);
		return $finder;
	}
}
