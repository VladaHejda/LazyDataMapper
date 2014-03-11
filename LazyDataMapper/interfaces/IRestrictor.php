<?php

namespace LazyDataMapper;

/**
 * @entityDependent
 */
interface IRestrictor
{

	/**
	 * @return mixed
	 */
	function getRestrictions();
}
