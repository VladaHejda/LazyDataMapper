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
	 * Get data for one entity. Returns loaded DataHolder.
	 * @param int $id
	 * @param Suggestor $suggestor
	 * @param DataHolder $dataHolder
	 * @return DataHolder
	 */
	function getById($id, Suggestor $suggestor, DataHolder $dataHolder = NULL);


	/**
	 * Get ids of matching entities.
	 * @param IRestrictor $restrictor
	 * @param int $limit ids count or NULL as unlimited (ignoring this argument does not affect the functionality
	 *        but if is used it can improve performance)
	 * @return int[]|NULL array of ids passing restrictions, when nothing pass, return NULL or empty array
	 */
	function getIdsByRestrictions(IRestrictor $restrictor, $limit = NULL);


	/**
	 * Get data for range of entities. Returns loaded DataHolder.
	 * @param int[] $ids
	 * @param Suggestor $suggestor
	 * @param DataHolder $dataHolder
	 * @return DataHolder
	 */
	function getByIdsRange(array $ids, Suggestor $suggestor, DataHolder $dataHolder = NULL);


	/**
	 * @param int $limit @see getIdsByRestrictions()
	 * @return mixed
	 * @todo add to DOC
	 */
	function getAllIds($limit = NULL);


	/**
	 * Saves modifications of entity.
	 * @param int $id
	 * @param DataHolder $holder
	 * @return void
	 */
	function save($id, DataHolder $holder);


	/**
	 * Creates new entity record.
	 * @param DataHolder $holder
	 * @return int id
	 */
	function create(DataHolder $holder);


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
