<?php

namespace LazyDataMapper\Tests\Caching;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\IceboxMapper;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Icebox.php';

class Test extends LazyDataMapper\Tests\AcceptanceTestCase
{

	/** @var Tests\IceboxFacade */
	private static $facade;


	public function testFirstGet()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\IceboxFacade($accessor, $serviceAccessor);
		self::$facade = $facade;

		$icebox = $facade->getById(4);

		$this->assertEquals('white', $icebox->color);
		$this->assertEquals(['color'], IceboxMapper::$lastSuggestor->getParamNames());

		$this->assertEquals(['egg', 'butter'], $icebox->food);
		$this->assertEquals(['food'], IceboxMapper::$lastSuggestor->getParamNames());

		// checks if getById is not called again
		$this->assertEquals('white', $icebox->color);
		$this->assertEquals(2, IceboxMapper::$calledGetById);

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

		$icebox = $facade->getById(5);

		$this->assertEquals('silver', $icebox->color);
		$this->assertEquals([], $icebox->food);

		// tests if getById called only once with right suggestions
		$this->assertEquals(1, IceboxMapper::$calledGetById);
		$this->assertEquals(['color', 'food'], IceboxMapper::$lastSuggestor->getParamNames());

		// tries get new data
		$this->assertEquals(25, $icebox->capacity);
		$this->assertEquals(2, IceboxMapper::$calledGetById);
		$this->assertEquals(['capacity'], IceboxMapper::$lastSuggestor->getParamNames());

		// tests if new suggestion cached
		$this->assertEquals(['color', 'food', 'capacity'], reset(reset($cache->cache)));
	}


	public function testGetNonexistent()
	{
		$this->assertNull(self::$facade->getById(99));
		$this->assertEquals(0, IceboxMapper::$calledGetById);
	}
}
