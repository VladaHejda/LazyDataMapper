<?php

namespace LazyDataMapper\Tests\GetContainer;

use LazyDataMapper,
	LazyDataMapper\Tests;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Icebox.php';

class Test extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testGet()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\IceboxFacade($accessor, $serviceAccessor);

		$iceboxes = $facade->getByIdsRange([2, 5, 8]);

		$this->assertInstanceOf('LazyDataMapper\Tests\Icebox', $iceboxes[0]);
		$this->assertInstanceOf('LazyDataMapper\Tests\Icebox', $iceboxes[1]);
		$this->assertInstanceOf('LazyDataMapper\Tests\Icebox', $iceboxes[2]);

		$this->assertEquals('black', $iceboxes[0]->color);
		$this->assertEquals('silver', $iceboxes[1]->color);
		$this->assertEquals('blue', $iceboxes[2]->color);

		$expected = [
			2 => ['beef steak', 'milk', 'egg'],
			5 => [],
			8 => ['jam'],
		];

		foreach ($iceboxes as $icebox) {
			$this->assertInstanceOf('LazyDataMapper\Tests\Icebox', $icebox);
			$this->assertEquals($expected[$icebox->getId()], $icebox->food);
		}

		$this->assertEquals(80, $iceboxes->capacity);
	}
}
