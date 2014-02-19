<?php

namespace LazyDataMapper;

/**
 * @entityDependent
 * @todo model could implement some rules for stacking / overriding / etc limits. It will be passed to some another user-implemented class.
 */
interface IRestrictor
{

	/**
	 * @return mixed
	 */
	function getRestrictions();
}
