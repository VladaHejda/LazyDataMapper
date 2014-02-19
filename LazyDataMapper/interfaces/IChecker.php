<?php

namespace LazyDataMapper;

/**
 * @entityDependent
 */
interface IChecker
{

	/**
	 * @param IDataEnvelope $subject IEntity when updating, IDataHolder when creating new Entity
	 * @return void
	 * @throws IntegrityException when integrity fails
	 */
	function check(IDataEnvelope $subject);
}
