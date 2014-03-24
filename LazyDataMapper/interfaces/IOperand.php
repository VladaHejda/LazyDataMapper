<?php

namespace LazyDataMapper;

interface IOperand
{

	/**
	 * @return IIdentifier
	 */
	function getIdentifier();


	/**
	 * @return Hierarchy
	 */
	function getHierarchy();
}
