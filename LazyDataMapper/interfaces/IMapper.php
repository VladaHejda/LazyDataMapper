<?php

namespace LazyDataMapper;

/**
 * Data source bind.
 * @entityDependent
 */
interface IMapper
{

	/**
	 * Checks when entity exists.
	 * @param int $id
	 * @return bool
	 */
	function exists($id);


	/**
	 * Get data for one entity.
	 * @param int $id
	 * @param ISuggestor $suggestor
	 * @return IDataHolder
	 */
	function getById($id, ISuggestor $suggestor);


	/**
	 * Get ids of matching entities.
	 * @maxCount 100 todo solve this
	 * @param IRestrictor $restrictor
	 * @return int[]|NULL array of ids passing restrictions, when nothing pass, return NULL or empty array
	 * @throws TooManyItemsException when count exceeds maxCount annotation limit
	 */
	function getIdsByRestrictions(IRestrictor $restrictor);


	/**
	 * Get data for range of entities.
	 * @param int[] $ids
	 * @param ISuggestor $suggestor
	 * @return IDataHolder
	 */
	function getByIdsRange(array $ids, ISuggestor $suggestor);


	/**
	 * Saves modifications of entity.
	 * @param int $id
	 * @param IDataHolder $holder
	 * @return void
	 */
	function save($id, IDataHolder $holder);


	/**
	 * Creates new entity record.
	 * @param IDataHolder $holder
	 * @return int id
	 */
	function create(IDataHolder $holder);


	/**
	 * Removes entity.
	 * @param int $id
	 * @return void
	 */
	function remove($id);
}
