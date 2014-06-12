<?php

namespace LazyDataMapper;

/**
 * Mapper that allows to use IMapper easy in simplest way. Requires implementing only methods exists() and getById().
 */
abstract class Mapper implements IMapper
{

	/**
	 * @param IRestrictor $restrictor
	 * @param int $limit
	 * @param int $offset
	 * @return int[]|NULL
	 * @throws NotImplementedException
	 */
	public function getIdsByRestrictions(IRestrictor $restrictor, $limit = NULL, $offset = NULL)
	{
		throw new NotImplementedException('Method ' . __METHOD__ . ' is not implemented yet.');
	}


	/**
	 * @param int[] $ids
	 * @param Suggestor $suggestor
	 * @param DataHolder $dataHolder
	 * @return DataHolder
	 * @throws NotImplementedException
	 */
	public function getByIdsRange(array $ids, Suggestor $suggestor, DataHolder $dataHolder = NULL)
	{
		throw new NotImplementedException('Method ' . __METHOD__ . ' is not implemented yet.');
	}


	/**
	 * @param int $limit
	 * @param int $offset
	 * @return mixed
	 * @throws NotImplementedException
	 */
	public function getAllIds($limit = NULL, $offset = NULL)
	{
		throw new NotImplementedException('Method ' . __METHOD__ . ' is not implemented yet.');
	}


	/**
	 * @param int $id
	 * @param DataHolder $holder
	 * @return void
	 * @throws NotImplementedException
	 */
	public function save($id, DataHolder $holder)
	{
		throw new NotImplementedException('Method ' . __METHOD__ . ' is not implemented yet.');
	}


	/**
	 * @param DataHolder $holder
	 * @return int id
	 * @throws NotImplementedException
	 */
	public function create(DataHolder $holder)
	{
		throw new NotImplementedException('Method ' . __METHOD__ . ' is not implemented yet.');
	}


	/**
	 * @param int $id
	 * @return void
	 * @throws NotImplementedException
	 */
	public function remove($id)
	{
		throw new NotImplementedException('Method ' . __METHOD__ . ' is not implemented yet.');
	}


	/**
	 * @param array $ids
	 * @return void
	 * @throws NotImplementedException
	 */
	public function removeByIdsRange(array $ids)
	{
		throw new NotImplementedException('Method ' . __METHOD__ . ' is not implemented yet.');
	}
}
