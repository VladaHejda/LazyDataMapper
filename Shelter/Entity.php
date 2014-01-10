<?php

namespace Shelter;

abstract class Entity implements IEntity
{

	public function __construct($id, array $data, $identifier, IOperand $parent = NULL, IAccessor $accessor)
	{
	}


	public function getParent()
	{
	}


	public function getIdentifier()
	{
	}


	/**
	 * @return int
	 */
	public function getId()
	{
	}


	/**
	 * @return array
	 */
	public function getChanges()
	{
	}
}
