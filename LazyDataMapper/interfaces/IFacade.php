<?php

namespace LazyDataMapper;

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
	 * @param int $id
	 */
	function remove($id);


	/**
	 * @param int[] $ids
	 */
	function removeByIdsRange(array $ids);


	/**
	 * @param IRestrictor $restrictor
	 */
	function removeByRestrictions(IRestrictor $restrictor);
}
