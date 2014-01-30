<?php

namespace Shelter;

abstract class EntityServiceAccessor implements IEntityServiceAccessor
{

	/**
	 * Cut "Facade" from Facade classname.
	 * @param Facade $facade
	 * @return string
	 * @throws Exception
	 */
	public function getEntityClass(Facade $facade)
	{
		$facadeClass = get_class($facade);
		if (strcasecmp(substr($facadeClass, -6), 'facade')) {
			throw new Exception("Expected Facade with classname <EntityName>Facade. $facadeClass given.");
		}
		return substr($facadeClass, 0, -6);
	}


	/**
	 * Makes plural from Entity classname.
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


	/**
	 * @param string $entityClass
	 * @param IIdentifier $parentIdentifier
	 * @param string $sourceParam
	 * @return IIdentifier
	 */
	public function composeIdentifier($entityClass, IIdentifier $parentIdentifier = NULL, $sourceParam = NULL)
	{
		return new Identifier($entityClass, (bool) $sourceParam, $parentIdentifier, $sourceParam);
	}


	/**
	 * By default there is no checker.
	 * @param string $entityClass
	 * @return IChecker|null
	 */
	public function getChecker($entityClass)
	{
		return NULL;
	}
}
