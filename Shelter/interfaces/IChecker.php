<?php

namespace Shelter;

/**
 * @entityDependent
 */
interface IChecker
{

	/**
	 * @param IEntity|null $entity or NULL during creating process
	 * @param IDataHolder $holder
	 * @return void
	 * @throws IntegrityException
	 */
	function check(IEntity $entity, IDataHolder $holder);
}
