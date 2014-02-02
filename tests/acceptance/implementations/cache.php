<?php

namespace Shelter\Tests\Cache;

class SimpleCache implements \Shelter\IExternalCache
{

	private $cache = array();


	public function save($key, $data)
	{
		$this->cache[$key] = $data;
	}


	public function load($key)
	{
		return isset($this->cache[$key]) ? $this->cache[$key] : NULL;
	}
}
