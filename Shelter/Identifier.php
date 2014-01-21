<?php

namespace Shelter;

/**
 * @todo possibly better to be static
 */
class Identifier implements IIdentifier
{

	/** @var int top level operand counter */
	static protected $counter = array();

	/** @var string */
	protected $identifier;


	/**
	 * Computes output identifier based on inputs.
	 * @param string $entityClass
	 * @param bool $isContainer
	 * @param string $parentIdentifier
	 * @param string $sourceParam
	 */
	public function __construct($entityClass, $isContainer = FALSE, $parentIdentifier = NULL, $sourceParam = NULL)
	{
		$identifier = $entityClass;
		$identifier .= $isContainer ? '*' : '';
		$identifier .= NULL !== $sourceParam ? "|$sourceParam" : '';
		$identifier .= NULL !== $parentIdentifier ? ">$parentIdentifier" : '';
		$this->identifier = $identifier;
	}


	/**
	 * @return string
	 */
	function composeIdentifier()
	{
		return $this->identifier;
	}
}
