<?php

namespace LazyDataMapper;

class SuggestorHelpers
{

	/**
	 * Encapsulates parameter names into `column`, `another_column` ...
	 */
	static function wrapColumns(array $paramNames)
	{
		return '`' . implode('`, `', $paramNames) . '`';
	}


	/**
	 * Encapsulates parameter names into 'column', 'another_column' ...
	 */
	static function wrapParams(array $paramNames)
	{
		return "'" . implode("', '", $paramNames) . "'";
	}
}
