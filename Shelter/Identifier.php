<?php

namespace Shelter;

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
	 * @param IIdentifier $parentIdentifier
	 * @param string $sourceParam
	 */
	public function __construct($entityClass, $isContainer = FALSE,  IIdentifier $parentIdentifier = NULL, $sourceParam = NULL)
	{
		$identifier = $entityClass;
		$identifier .= $isContainer ? '*' : '';
		$identifier .= NULL !== $sourceParam ? "|$sourceParam" : '';
		if ($parentIdentifier) {
			$identifier .= '>' . $parentIdentifier->getKey();
		} else {
			$counterKey = $isContainer ? "$entityClass*" : $entityClass;
			if (!isset(static::$counter[$counterKey])) {
				static::$counter[$counterKey] = 0;
			}
			$identifier .= '#' . static::$counter[$counterKey]++;
		}
		$this->identifier = $identifier;
	}


	/**
	 * @return string
	 */
	function getKey()
	{
		return $this->identifier;
	}
}
