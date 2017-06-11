<?php

namespace GoldenPlanet\WebpackBundle\Provider\DirectoryProvider;

class ConfiguredDirectoryProvider implements DirectoryProviderInterface
{
	private $directories;

	public function __construct(array $directories)
	{
		$this->directories = $directories;
	}

	public function getDirectories()
	{
		return $this->directories;
	}
}
