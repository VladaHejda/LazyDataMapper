<?php

namespace LazyDataMapper\Tests\Caching;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\KitchenMapper,
	LazyDataMapper\Tests\IceboxMapper;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Kitchen.php';
require_once __DIR__ . '/implementations/model/Icebox.php';

class DescendantsTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testFirstGet()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\KitchenFacade($accessor, $serviceAccessor);

		$kitchen = $facade->getById(1);

		$this->assertInstanceOf('LazyDataMapper\Tests\Icebox', $kitchen->icebox);
		$this->assertEquals(22, $kitchen->area);

		$this->assertEquals(45, $kitchen->icebox->capacity);

		return $facade;
	}


	/**
	 * @depends testFirstGet
	 */
	public function testCaching(Tests\KitchenFacade $facade)
	{
		$kitchen = $facade->getById(2);

		$this->assertInstanceOf('LazyDataMapper\Tests\Icebox', $kitchen->icebox);
		$this->assertEquals(54, $kitchen->area);
		$this->assertEquals(25, $kitchen->icebox->capacity);

		// tests counts of getById calls
		$this->assertEquals(1, KitchenMapper::$calledGetById);
		$this->assertEquals(1, IceboxMapper::$calledGetById);

		// tests suggestions
		$this->assertEquals(['icebox', 'area'], KitchenMapper::$lastSuggestor->getParamNames());
		$this->assertTrue(KitchenMapper::$lastSuggestor->hasDescendants());
		$this->assertTrue(KitchenMapper::$lastSuggestor->hasDescendant('LazyDataMapper\Tests\Icebox', $source));
		$this->assertEquals('icebox', $source);
		$descendant = KitchenMapper::$lastSuggestor->getDescendant('LazyDataMapper\Tests\Icebox', $source);
		$this->assertInstanceOf('LazyDataMapper\ISuggestor', $descendant);
		$this->assertEquals(['capacity'], $descendant->getParamNames());
	}
}
