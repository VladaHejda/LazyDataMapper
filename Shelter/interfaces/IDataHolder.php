<?php

namespace Shelter;

/**
 * Based on Suggestor gains data from Mapper and gives data to Mapper's method save() and create().
 */
interface IDataHolder extends \Iterator
{

	/**
	 * @param array $params
	 * @return void
	 */
	function setParams(array $params);


	/**
	 * @param string $type
	 * @return array
	 */
	function getParams($type = NULL);


	/**
	 * @param string $type
	 * @return bool
	 * @throws Exception on unknown type
	 */
	function isDataOnType($type);


	/**
	 * @param string $entityClass
	 * @param string $sourceParam
	 * @return self
	 * @throws Exception on empty or undefined descendant
	 */
	function getDescendant($entityClass, $sourceParam = NULL);


	/**
	 * @return ISuggestor
	 */
	function getSuggestor();
}
