<?php

namespace LazyDataMapper;

interface IIdentifier
{

	/** root operands origins */
	const BY_ID = '',
		BY_RESTRICTIONS = '*',
		BY_IDS_RANGE = '@',
		ONE_BY_RESTRICTIONS = '^',
		CREATE = '+'
	;


	/**
	 * @return string
	 */
	function getKey();
}
