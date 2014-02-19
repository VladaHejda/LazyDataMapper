<?php

namespace LazyDataMapper\Tests\Remove;

use LazyDataMapper,
	LazyDataMapper\Tests;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Icebox.php';

class Test extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testRemove()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\IceboxFacade($accessor, $serviceAccessor);

		$facade->remove(5);

		$this->assertNull($facade->getById(5));
		$this->assertInstanceOf('LazyDataMapper\Tests\Icebox', $facade->getById(4));
	}
}
