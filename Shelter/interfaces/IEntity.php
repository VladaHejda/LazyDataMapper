<?php

namespace Shelter;

/**
 * @entityDependent
 */
interface IEntity extends IOperand, IDataEnvelope
{

	/**
	 * @return int
	 */
	function getId();


	/**
	 * @param string $paramName
	 * @return bool
	 */
	function isChanged($paramName = NULL);


	/**
	 * @return array
	 */
	function getChanges();


	/**
	 * @param string $paramName
	 * @return mixed
	 */
	function getOriginal($paramName);


	/**
	 * @param string $paramName
	 * @return void
	 */
	function reset($paramName = NULL);


	/**
	 * @return void
	 */
	function save();
}
