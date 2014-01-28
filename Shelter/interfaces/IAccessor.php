<?php

namespace Shelter;

/**
 * The main class leading all dependencies.
 */
interface IAccessor
{

	/********************* interface for IFacade *********************/

	/**
	 * @param array|string $entityClass
	 * @param int $id
	 * @param IOperand $parent
	 * @param string $sourceParam
	 * @return IEntity
	 */
	function getById($entityClass, $id, IOperand $parent = NULL, $sourceParam = NULL);


	/**
	 * @param array|string $entityClass
	 * @param IRestrictor $restrictor
	 * @param IOperand $parent
	 * @return IEntityContainer
	 */
	function getByRestrictions($entityClass, IRestrictor $restrictor, IOperand $parent = NULL);


	/**
	 * @param array|string $entityClass
	 * @param array $data
	 * @param bool $check whether check created Entity by Checker.
	 * @return IEntity
	 */
	function create($entityClass, array $data, $check = TRUE);


	/**
	 * @param array|string $entityClass
	 * @param int $id
	 */
	function remove($entityClass, $id);


	/********************* interface for IEntity *********************/


	/**
	 * @param IEntity $entity
	 * @param string $paramName
	 * @return bool
	 */
	function hasParam(IEntity $entity, $paramName);


	/**
	 * @param IEntity $entity
	 * @param string $paramName
	 * @return string
	 */
	function getParam(IEntity $entity, $paramName);


	/**
	 * @param IEntity $entity
	 * @return void
	 */
	function save(IEntity $entity);
}
