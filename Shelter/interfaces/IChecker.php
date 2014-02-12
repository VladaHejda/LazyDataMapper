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
	 * todo co když checker (při sejvu, create entitu nedostane) entitu změní!?
	 */
	function check($entity, IDataHolder $holder);
}
