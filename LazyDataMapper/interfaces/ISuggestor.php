<?php

namespace LazyDataMapper;

/**
 * Suggests parameter names and descendants to Mapper.
 * @todo some param helpers? (e.g. helper for creating 'param', 'param2', ... from paramNames array)
 */
interface ISuggestor extends \Iterator
{

	/**
	 * @param string $group
	 * @return bool
	 */
	function isSuggestedGroup($group);


	/**
	 * If grouped but group is omitted, it returns all param names merged.
	 * @param string $group
	 * @return string[]
	 */
	function getParamNames($group = NULL);


	/**
	 * @return IIdentifier
	 */
	function getIdentifier();


	/**
	 * Says whether has at least one descendant.
	 * @return bool
	 */
	function hasDescendants();


	/**
	 * @param string $entityClass
	 * @param string $sourceParam
	 * @return bool
	 */
	function hasDescendant($entityClass, &$sourceParam = NULL);


	/**
	 * @param string $entityClass
	 * @param string $sourceParam
	 * @return self|null
	 */
	function getDescendant($entityClass, &$sourceParam = NULL);


	/**
	 * @param string $entityClass
	 * @param string $sourceParam
	 * @return IIdentifier
	 * @throws Exception when descendant does not exist
	 */
	function getDescendantIdentifier($entityClass, $sourceParam);


	/**
	 * @return IParamMap
	 */
	function getParamMap();
}
