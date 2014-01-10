<?php

namespace Shelter;

class RequestKey implements IRequestKey
{

	/**
	 * @return string
	 */
	public function getKey()
	{
		$input = $extra = '';

		// CLI
		if ('cli' === PHP_SAPI) {
			$extra = implode(' ', array_slice($_SERVER['argv'], 1));
		}

		if (isset($_SERVER['REQUEST_URI'])) {
			$input = $_SERVER['REQUEST_URI'];

		} elseif (isset($_SERVER['PHP_SELF'])) {
			$input = $_SERVER['PHP_SELF'];

		} elseif (isset($_SERVER['SCRIPT_NAME'])) {
			$input = $_SERVER['SCRIPT_NAME'];

		} elseif (isset($_SERVER['SCRIPT_FILENAME'])) {
			$input = $_SERVER['SCRIPT_FILENAME'];
		}

		if (!empty($extra)) {
			$input .= $extra;
		}
		return substr(md5($input), 8, 16);
	}
}
