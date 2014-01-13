<?php

namespace Shelter;

interface IEntityServiceAccessor
{

	/**
	 * Apply solution to gain Entity ParamMap service based on Entity class name.
	 * @param string $entityClass
	 * @return IParamMap
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
	 * Apply solution to gain Entity Container classname based on Entity class name.
	 * NOTICE that this method opposed to others returns just string classname, not the instance!
	 * @param string $entityClass
	 * @return string
	 */
	function getEntityContainerClass($entityClass);
}
