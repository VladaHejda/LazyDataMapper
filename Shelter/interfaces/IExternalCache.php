<?php

namespace Shelter;

/**
 * Apply your own caching.
 */
interface IExternalCache
{

	/**
	 * @param string $key
	 * @param mixed $data
	 * @return void
	 */
	function save($key, $data);


	/**
	 * @param string $key
	 * @return mixed
	 */
	function load($key);
}
