<?php

namespace Shelter;

class Suggestor implements ISuggestor
{

	/**
	 * @param IParamMap $paramMap
	 * @param ISuggestorCache $cache
	 * @param array $suggestions
	 * @param array $descendants entityClass => identifier
	 */
	public function __construct(IParamMap $paramMap, ISuggestorCache $cache, array $suggestions, $identifier = NULL, array $descendants = array())
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
	 * @return string
	 */
	public function getIdentifier()
	{
	}


	/**
	 * @return bool
	 */
	public function hasDescendants()
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


	/**
	 * @return IParamMap
	 */
	public function getParamMap()
	{
	}
}
