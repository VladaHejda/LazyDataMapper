<?php

namespace Shelter;

/**
 * Suggestor cache. Caches suggestions per deterministic request (see IRequestKey)
 * for later efficient data load.
 */
interface ISuggestorCache
{

	const PARAM_NAMES = 0,
		DESCENDANTS = 1;


	/**
	 * Adds parameter name under one identifier.
	 * @param string $identifier
	 * @param string $paramName
	 * @param string $entityClass
	 * @return ISuggestor with one suggestion of cached parameter name
	 */
	function cacheParamName($identifier, $paramName, $entityClass);


	/**
	 * Adds descendant under one identifier.
	 * @param string $identifier
	 * @param string $descendantEntityClass
	 * @param string $sourceParam
	 * @return void
	 */
	function cacheDescendant($identifier, $descendantEntityClass, $sourceParam = NULL);


	/**
	 * Gets all cached suggestions under one identifier.
	 * @param string $identifier
	 * @param string $entityClass
	 * @return ISuggestor
	 */
	function getCached($identifier, $entityClass);
}
