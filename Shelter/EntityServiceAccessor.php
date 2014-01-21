<?php

namespace Shelter;

abstract class EntityServiceAccessor implements IEntityServiceAccessor
{

	/**
	 * @param Facade $facade
	 * @return string
	 */
	public function getEntityClass(Facade $facade)
	{
		return substr(get_class($facade), 0, -6);
	}


	/**
	 * Makes plural from Entity class name.
	 * @param string $entityClass
	 * @return string
	 */
	public function getEntityContainerClass($entityClass)
	{
		$len = strlen($entityClass);
		if ('y' === $entityClass[$len-1]) {
			$entityClass[$len-1] = 'i';
			return $entityClass . 'es';
		}
		return $entityClass . 's';
	}


	// todo použít v accessoru
	public function composeIdentifier($entityClass, $parentIdentifier = NULL, $sourceParam = NULL)
	{
		$identifier = new Identifier($entityClass, (bool) $sourceParam, $parentIdentifier, $sourceParam);
		return $identifier->composeIdentifier();
	}
}
