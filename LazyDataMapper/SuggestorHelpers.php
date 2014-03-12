<?php

namespace LazyDataMapper;

class SuggestorHelpers
{

	/**
	 * Encapsulates parameter names into `column`, `another_column` ...
	 */
	static function wrapColumns(array $paramNames, $extra = '')
	{
		if (!empty($extra)) {
			$extra = " $extra";
		}
		return '`' . implode("`$extra, `", $paramNames) . "`$extra";
	}


	/**
	 * Encapsulates parameter names into 'column', 'another_column' ...
	 */
	static function wrapParams(array $paramNames)
	{
		return "'" . implode("', '", $paramNames) . "'";
	}
}
