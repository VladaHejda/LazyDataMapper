<?php

namespace Shelter;

interface IOperand
{

	/**
	 * @return string
	 */
	function getIdentifier();


	/**
	 * @return IOperand|NULL
	 */
	function getParent();
}
