<?php

namespace Shelter;

/**
 * @entityDependent
 */
interface IParamMap
{

	/**
	 * @param string $type
	 * @return array one or two dimensional array (dependent on whether requesting type or not)
	 * @throws Exception when requesting type even if not separated by type
	 * @throws Exception on unknown type
	 */
	function getMap($type = NULL);


	/**
	 * @return bool
	 */
	function isSeparatedByType();


	/**
	 * @param string $type
	 * @return bool
	 * @throws Exception if is not separated by type
	 */
	function hasType($type);


	/**
	 * @param string $paramName
	 * @return string
	 * @throws Exception on unknown param name
	 */
	function getParamType($paramName);
}
