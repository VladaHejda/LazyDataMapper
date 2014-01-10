<?php

namespace Shelter;

class DataHolder implements IDataHolder
{

	/**
	 * @param ISuggestor $suggestor
	 * @param array $ids for container holder
	 */
	public function __construct(ISuggestor $suggestor, array $ids = NULL)
	{
	}


	/**
	 * @experimental
	 * Strict mode accepts only setting suggested parameters.
	 * Otherwise accepts to set all parameters from map independent on what has been suggested.
	 */
	public function setStrictMode($on = TRUE)
	{
	}


	/**
	 * @param array|array[] $params array for one; array of arrays for container, indexed by id
	 * @return void
	 */
	public function setParams(array $params)
	{
	}


	/**
	 * @param string $type
	 * @return array
	 */
	public function getParams($type = NULL)
	{
	}


	/**
	 * @param string $type
	 * @return bool
	 */
	public function isDataOnType($type)
	{
	}


	/**
	 * @param string $entityClass
	 * @param string $sourceParam
	 * @return self
	 */
	public function getDescendant($entityClass, $sourceParam = NULL)
	{
	}


	/**
	 * @return ISuggestor
	 */
	public function getSuggestor()
	{
	}


	public function rewind()
	{
	}


	public function valid()
	{
	}


	public function current()
	{
	}


	public function key()
	{
	}


	public function next()
	{
	}
}
