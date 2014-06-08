<?php

namespace LazyDataMapper\Tests\Entity;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\SuggestorCache;

require_once __DIR__ . '/implementations/model/Car.php';

class ForceSuggestionsTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testForceKey()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\CarFacade($accessor, $serviceAccessor);

		$suggestorCache->forceSuggestions('a');

		$car = $facade->getById(3);

		$this->assertEquals('Mustang', $car->name);
		$this->assertEquals('coupe', $car->chassis);
		$this->assertEquals(19400, $car->price);
		$this->assertEquals(3, SuggestorCache::$calledCacheSuggestion);

		return [$suggestorCache, $facade];
	}


	/**
	 * @depends testForceKey
	 */
	public function testAnotherForceKey(array $services)
	{
		list($suggestorCache, $facade) = $services;

		$suggestorCache->forceSuggestions('b');

		$car = $facade->getById(1);

		$this->assertEquals('Z4', $car->name);
		$this->assertEquals('roadster', $car->chassis);
		$this->assertEquals(2, SuggestorCache::$calledCacheSuggestion);

		return [$suggestorCache, $facade];
	}


	/**
	 * @depends testAnotherForceKey
	 */
	public function testForcedKeyCache(array $services)
	{
		list($suggestorCache, $facade) = $services;

		$suggestorCache->forceSuggestions('a');

		$car = $facade->getById(2);

		$this->assertEquals('Gallardo', $car->name);
		$this->assertEquals('coupe', $car->chassis);
		$this->assertEquals(184000, $car->price);
		$this->assertEquals(0, SuggestorCache::$calledCacheSuggestion);

		return [$suggestorCache, $facade];
	}


	/**
	 * @depends testForcedKeyCache
	 */
	public function testAnotherForcedKeyCache(array $services)
	{
		list($suggestorCache, $facade) = $services;

		$suggestorCache->forceSuggestions('b');

		$car = $facade->getById(2);

		$this->assertEquals('Gallardo', $car->name);
		$this->assertEquals('coupe', $car->chassis);
		$this->assertEquals(0, SuggestorCache::$calledCacheSuggestion);

		$this->assertEquals(184000, $car->price);
		$this->assertEquals(1, SuggestorCache::$calledCacheSuggestion);
	}
}
