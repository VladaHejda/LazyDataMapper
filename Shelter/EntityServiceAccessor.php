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


	// todo return IIdentifier and call composeIdentifier only when is really needed?
	public function composeIdentifier($entityClass, $parentIdentifier = NULL, $sourceParam = NULL)
	{
		$identifier = new Identifier($entityClass, (bool) $sourceParam, $parentIdentifier, $sourceParam);
		return $identifier->composeIdentifier();
	}
}
