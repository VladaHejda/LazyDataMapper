<?php

namespace LazyDataMapper\Tests\Cache;

class SimpleCache implements \LazyDataMapper\IExternalCache
{

	public $cache = [];


	public function save($key, $data)
	{
		$this->cache[$key] = $data;
	}


	public function load($key)
	{
		return isset($this->cache[$key]) ? $this->cache[$key] : NULL;
	}
}
