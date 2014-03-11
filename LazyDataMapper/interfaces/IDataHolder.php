<?php

namespace LazyDataMapper;

/**
 * Based on Suggestor gains data from Mapper and gives data to Mapper's method save() and create().
 */
interface IDataHolder extends \Iterator
{

	/**
	 * @param array $params
	 * @return self provides fluent interface
	 */
	function setParams(array $params);


	/**
	 * @param string $group
	 * @return array
	 */
	function getParams($group = NULL);


	/**
	 * @param string $group
	 * @return bool
	 * @throws Exception on unknown group
	 */
	function isDataInGroup($group);


	/**
	 * @param string $entityClass
	 * @param string $sourceParam
	 * @return self|NULL
	 */
	function getDescendant($entityClass, &$sourceParam = NULL);


	/**
	 * @return bool
	 */
	public function isContainer();


	/**
	 * @return ISuggestor
	 */
	function getSuggestor();
}
