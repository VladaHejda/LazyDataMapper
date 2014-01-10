<?php

namespace Shelter;

/**
 * @todo if there will not be a way how to use own extended Identifier (though it would be a shame)
 *       it will be good if class will be declared as final
 */
class Identifier implements IIdentifier
{

	/** @var int top level operand counter */
	static protected $counter = array();


	/**
	 * Computes output identifier based on inputs.
	 * @param string $entityClass
	 * @param bool $isContainer
	 * @param string $parentIdentifier
	 * @param string $sourceParam
	 */
	public function __construct($entityClass, $isContainer = FALSE, $parentIdentifier = NULL, $sourceParam = NULL)
	{
	}


	/**
	 * @return string
	 */
	function composeIdentifier()
	{
	}
}
