<?php

namespace Shelter;

class Suggestor implements ISuggestor
{

	public function __construct(IParamMap $paramMap, ISuggestorCache $cache, array $suggestions, $identifier = NULL, $sourceParam = NULL, array $descendants = array())
	{
	}


	/**
	 * @param string $type
	 * @return bool
	 */
	public function isSuggestedType($type)
	{
	}


	/**
	 * @param string $type
	 * @return string[]
	 */
	public function getParamNames($type = NULL)
	{
	}


	/**
	 * @param string $name
	 * @param string $sourceParam
	 * @return bool
	 */
	public function hasDescendant($name, $sourceParam = NULL)
	{
	}


	/**
	 * @param string $entityClass
	 * @param string $sourceParam
	 * @return self
	 */
	public function getDescendant($entityClass, $sourceParam = NULL)
	{
	}
}
