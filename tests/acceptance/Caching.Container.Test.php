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

		// checks calls count
		$this->assertEquals(0, IceboxMapper::$calledGetByRestrictions);
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
		list($cache, $facade) = $services;

		$iceboxes = $facade->getByIdsRange([5, 4]);

		$this->assertEquals('silver', $iceboxes[0]->color);
		$this->assertEquals('white', $iceboxes[1]->color);
		$this->assertEquals([], $iceboxes[0]->food);
		$this->assertEquals(['egg', 'butter'], $iceboxes[1]->food);

		// tests if getById called only once with right suggestions
		$this->assertEquals(1, IceboxMapper::$calledGetByRestrictions);
		$this->assertEquals(0, IceboxMapper::$calledGetById);

		// tries get new data
		$this->assertTrue($iceboxes[0]->freezer);

		// tests if new suggestion cached
		$this->assertEquals(['color', 'food', 'freezer'], reset(reset($cache->cache)));
	}
}
