<?php

namespace LazyDataMapper;

/**
 * @entityDependent
 */
interface IChecker
{

	/**
	 * @param IEntity $entity
	 * @return void
	 * @throws IntegrityException when integrity fails
	 */
	function check(IEntity $entity);
}
