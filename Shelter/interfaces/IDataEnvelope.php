<?php

namespace Shelter;

interface IDataEnvelope
{

	/**
	 * @param string $paramName
	 * @return mixed
	 */
	function __get($paramName);
}
