<?php

namespace Shelter;

/**
 * Suggests parameter names and descendants to Mapper.
 * @todo some param helpers? (e.g. helper for creating 'param', 'param2', ... from paramNames array)
 */
interface ISuggestor extends \Iterator
{

	/**
	 * @param string $type
	 * @return bool
	 */
	function isSuggestedType($type);


	/**
	 * If is separated by type but type is omitted, it returns all param names merged.
	 * @param string $type
	 * @return string[]
	 */
	function getParamNames($type = NULL);


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
