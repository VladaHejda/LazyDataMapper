<?php

namespace Shelter\Tests\Caching;

use Shelter,
	Shelter\Tests,
	Shelter\Tests\IceboxMapper;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Icebox.php';

class ContainerTest extends Shelter\Tests\TestCase
{

	public function testFirstGet()
	{
		$requestKey = new Shelter\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new Shelter\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new Shelter\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\IceboxFacade($accessor, $serviceAccessor);

		$iceboxes = $facade->getByIdsRange([4, 8]);

		$this->assertEquals('white', $iceboxes[0]->color);
		$this->assertEquals('blue', $iceboxes[1]->color);
		$this->assertEquals(['egg', 'butter'], $iceboxes[0]->food);
		$this->assertEquals(['jam'], $iceboxes[1]->food);

		// checks getByIdsRange called count
		$this->assertEquals(4, IceboxMapper::$calledGetById);

		// tests if suggestions cached
		$this->assertCount(1, $cache->cache);
		$this->assertEquals(['color', 'food'], reset(reset($cache->cache)));

		return [$cache, $facade];
	}


	/**
	 * @depends testFirstGet
	 */
	public function testCaching(array $services)
	{
		$this->markTestIncomplete();

		list($cache, $facade) = $services;
	}
}
