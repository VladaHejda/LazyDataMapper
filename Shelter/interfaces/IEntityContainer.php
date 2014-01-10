<?php

namespace Shelter;

/**
 * @entityDependent
 */
interface IEntityContainer extends IOperand, \ArrayAccess, \Iterator, \Countable
{

	/**
	 * @param string $paramName
	 * @return array
	 */
	function getParams($paramName);
}
