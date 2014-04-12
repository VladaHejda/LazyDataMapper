<?php

namespace LazyDataMapper;

interface IEntityServiceAccessor
{

	/**
	 * Apply solution to gain Entity ParamMap service based on Entity class name.
	 * @param string $entityClass
	 * @return ParamMap
	 */
	function getParamMap($entityClass);


	/**
	 * Apply solution to gain Entity Mapper service based on Entity class name.
	 * @param string $entityClass
	 * @return IMapper
	 */
	function getMapper($entityClass);


	/**
	 * Apply solution to gain Entity Checker service based on Entity class name.
	 * When Entity has no checker, return NULL.
	 * @param string $entityClass
	 * @return IChecker|null
	 */
	function getChecker($entityClass);


	/**
	 * Apply solution to gain Entity classname based on Facade.
	 * NOTICE that this method opposed to others returns just string classname, not the instance!
	 * @param Facade $facade
	 * @return string
	 */
	function getEntityClass(Facade $facade);


	/**
	 * Apply solution to gain Entity Collection classname based on Entity class name.
	 * NOTICE that this method opposed to others returns just string classname, not the instance!
	 * @param string $entityClass
	 * @return string
	 */
	function getEntityCollectionClass($entityClass);


	/**
	 * Compose persistent identifier based on input arguments. It should be arbitrary distinctive string key.
	 * @param string $entityClass
	 * @param bool $isCollection
	 * @param IIdentifier $parentIdentifier
	 * @param string $sourceParam
	 * @return IIdentifier
	 */
	function composeIdentifier($entityClass, $isCollection = FALSE, IIdentifier $parentIdentifier = NULL, $sourceParam = NULL);
}
