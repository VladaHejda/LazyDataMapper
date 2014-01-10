<?php

namespace Shelter;

abstract class ParamMap implements IParamMap
{

	/** @var array set this map in descendant */
	protected $map = array();


	/**
	 * @return array
	 */
	public function getMap()
	{
	}
}
