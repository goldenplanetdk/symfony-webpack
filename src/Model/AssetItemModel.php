<?php

namespace GoldenPlanet\WebpackBundle\Model;

class AssetItemModel
{
	/**
	 * @var string
	 */
	private $resource;

	/**
	 * @var string|null
	 */
	private $group;

	/**
	 * @return string
	 */
	public function getResource()
	{
		return $this->resource;
	}

	/**
	 * @param string $resource
	 */
	public function setResource($resource)
	{
		$this->resource = $resource;
	}

	/**
	 * @return null|string
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * @param null|string $group
	 */
	public function setGroup($group)
	{
		$this->group = $group;
	}
}
