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
	 * @param int[] $ids
	 * @return IEntityContainer
	 */
	public function getByIdsRange(array $ids);


	/**
	 * @param IRestrictor $restrictor
	 * @return IEntityContainer
	 */
	function getByRestrictions(IRestrictor $restrictor);


	/**
	 * @return IEntity
	 */
	function create();


	/**
	 * @param int $id
	 */
	function remove($id);
}
