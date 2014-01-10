<?php

namespace Shelter;

/**
 * @entityDependent
 */
interface IEntity extends IOperand
{

	/**
	 * @return int
	 */
	function getId();


	/**
	 * @return array
	 */
	function getChanges();
}
