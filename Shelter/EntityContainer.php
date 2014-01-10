<?php

namespace Shelter;

abstract class EntityContainer implements IEntityContainer
{

	public function __construct(array $data, $identifier, IOperand $parent = NULL, IAccessor $accessor, $entityClass)
	{
	}


	public function getParent()
	{
	}


	public function getIdentifier()
	{
	}


	public function getParams($paramName)
	{
	}
}
