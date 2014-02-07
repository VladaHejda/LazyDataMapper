<?php

namespace Shelter\Tests\Caching;

use Shelter,
	Shelter\Tests,
	Shelter\Tests\KitchenMapper,
	Shelter\Tests\IceboxMapper;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Kitchen.php';
require_once __DIR__ . '/implementations/model/Icebox.php';

class DescendantsTest extends Shelter\Tests\TestCase
{

	public function testFirstGet()
	{
		$requestKey = new Shelter\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new Shelter\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new Shelter\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\KitchenFacade($accessor, $serviceAccessor);

		$kitchen = $facade->getById(1);

		$this->assertInstanceOf('Shelter\Tests\Icebox', $kitchen->icebox);
		$this->assertEquals(22, $kitchen->area);

		$this->assertEquals(45, $kitchen->icebox->capacity);

		return [$cache, $facade];
	}


	/**
	 * @depends testFirstGet
	 */
	public function testCaching(array $services)
	{
		list($cache, $facade) = $services;
		KitchenMapper::$calledGetById = 0;
		IceboxMapper::$calledGetById = 0;

		// force cache
		foreach ($cache->cache as $originalKey => $cached) {
			$newKey = $originalKey;
			$newKey[strlen($newKey) -1] = 1;
			$cache->cache[$newKey] = $cached;
		}

		$kitchen = $facade->getById(2);

		$this->assertInstanceOf('Shelter\Tests\Icebox', $kitchen->icebox);
		$this->assertEquals(54, $kitchen->area);
		$this->assertEquals(25, $kitchen->icebox->capacity);

		// tests counts of getById calls
		$this->assertEquals(1, KitchenMapper::$calledGetById);
		$this->assertEquals(1, IceboxMapper::$calledGetById);

		// tests suggestions
		$this->assertEquals(['icebox', 'area'], KitchenMapper::$lastSuggestor->getParamNames());
		$this->assertTrue(KitchenMapper::$lastSuggestor->hasDescendants());
		$this->assertTrue(KitchenMapper::$lastSuggestor->hasDescendant('Shelter\Tests\Icebox', $source));
		$this->assertEquals('icebox', $source);
		$descendant = KitchenMapper::$lastSuggestor->getDescendant('Shelter\Tests\Icebox', $source);
		$this->assertInstanceOf('Shelter\ISuggestor', $descendant);
		$this->assertEquals(['capacity'], $descendant->getParamNames());
	}
}
