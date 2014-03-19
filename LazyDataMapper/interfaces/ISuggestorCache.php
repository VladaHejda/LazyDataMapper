<?php

namespace LazyDataMapper;

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
	 * @param IIdentifier $identifier
	 * @param string $paramName
	 * @param string $entityClass
	 * @return ISuggestor with one suggestion of cached parameter name
	 */
	function cacheParamName(IIdentifier $identifier, $paramName, $entityClass);


	/**
	 * Adds descendant under one identifier.
	 * @param IIdentifier $identifier
	 * @param string $descendantEntityClass
	 * @param string $sourceParam
	 * @param bool $isContainer
	 * @return void
	 */
	function cacheDescendant(IIdentifier $identifier, $descendantEntityClass, $sourceParam, $isContainer = FALSE);


	/**
	 * Gets all cached suggestions under one identifier or NULL when nothing cached.
	 * @param IIdentifier $identifier
	 * @param string $entityClass
	 * @param bool $isContainer
	 * @return ISuggestor|NULL
	 */
	function getCached(IIdentifier $identifier, $entityClass, $isContainer = NULL);
}
