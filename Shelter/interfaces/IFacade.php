<?php

namespace Shelter;

/**
 * @entityDependent
 */
interface IFacade
{

	/**
	 * @param int $id
	 * @return IEntity
	 */
	function getById($id);


	/**
	 * @param IRestrictor|int[] $restrictor
	 * @return IEntityContainer
	 */
	function getByRestrictions($restrictor);


	/**
	 * @return IEntity
	 */
	function create();


	/**
	 * @param int $id
	 */
	function remove($id);
}
