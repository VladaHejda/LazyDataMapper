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
	 * Get data for one entity. Returns loaded IDataHolder.
	 * @param int $id
	 * @param ISuggestor $suggestor
	 * @param IDataHolder $dataHolder
	 * @return IDataHolder
	 */
	function getById($id, ISuggestor $suggestor, IDataHolder $dataHolder = NULL);


	/**
	 * Get ids of matching entities.
	 * @param IRestrictor $restrictor
	 * @param int $limit ids count or NULL as unlimited (ignoring this argument does not affect the functionality
	 *        but if is used it can improve performance)
	 * @return int[]|NULL array of ids passing restrictions, when nothing pass, return NULL or empty array
	 */
	function getIdsByRestrictions(IRestrictor $restrictor, $limit = NULL);


	/**
	 * Get data for range of entities. Returns loaded IDataHolder.
	 * @param int[] $ids
	 * @param ISuggestor $suggestor
	 * @param IDataHolder $dataHolder
	 * @return IDataHolder
	 */
	function getByIdsRange(array $ids, ISuggestor $suggestor, IDataHolder $dataHolder = NULL);


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


	/**
	 * @param array $ids
	 * @return void
	 */
	function removeByIdsRange(array $ids);
}
