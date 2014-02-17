<?php

namespace Shelter\Tests\Remove;

use Shelter,
	Shelter\Tests;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Icebox.php';

class Test extends Shelter\Tests\AcceptanceTestCase
{

	public function testRemove()
	{
		$requestKey = new Shelter\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new Shelter\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new Shelter\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\IceboxFacade($accessor, $serviceAccessor);

		$facade->remove(5);

		$this->assertNull($facade->getById(5));
		$this->assertInstanceOf('Shelter\Tests\Icebox', $facade->getById(4));
	}
}
