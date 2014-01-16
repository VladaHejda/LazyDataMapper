<?php

namespace Shelter;

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
