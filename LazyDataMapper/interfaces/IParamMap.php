<?php

namespace LazyDataMapper;

/**
 * @entityDependent
 */
interface IParamMap
{

	/**
	 * @param string $group
	 * @param bool $flip when TRUE method returns parameter names in keys and NULL in values (prepared for fill),
	 *      otherwise parameter names are in values.
	 * @return array one or two dimensional array (dependent on whether requesting group or not)
	 * @throws Exception when requesting group even if not grouped
	 * @throws Exception on unknown group
	 */
	function getMap($group = NULL, $flip = TRUE);


	/**
	 * @return bool
	 */
	function isGrouped();


	/**
	 * @param string $paramName
	 * @return bool
	 */
	function hasParam($paramName);


	/**
	 * @param string $group
	 * @return bool
	 * @throws Exception if is not grouped
	 */
	function hasGroup($group);


	/**
	 * @param string $paramName
	 * @return string
	 * @throws Exception on unknown param name
	 */
	function getParamGroup($paramName);


	/**
	 * @param string $paramName
	 * @return mixed
	 * @throws Exception on unknown param name
	 */
	function getDefaultValue($paramName);
}
