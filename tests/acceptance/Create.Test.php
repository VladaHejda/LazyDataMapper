<?php

namespace Shelter\Tests\Create;

use Shelter,
	Shelter\Tests,
	Shelter\Tests\IceboxMapper;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Icebox.php';

class Test extends Shelter\Tests\TestCase
{

	public function testCreate()
	{
		$requestKey = new Shelter\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new Shelter\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new Shelter\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\IceboxFacade($accessor, $serviceAccessor);

		$icebox = $facade->create();
		$this->assertInstanceOf('Shelter\Tests\Icebox', $icebox);
		$icebox = $facade->getById($icebox->getId());
		$this->assertInstanceOf('Shelter\Tests\Icebox', $icebox);

		$this->assertEquals(0, IceboxMapper::$calledGetById);
	}


	public function testCreateWithData()
	{
		$requestKey = new Shelter\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new Shelter\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new Shelter\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\IceboxFacade($accessor, $serviceAccessor);

		$icebox = $facade->create(['color' => 'yellow', 'repairs' => '6', ]);
		$this->assertInstanceOf('Shelter\Tests\Icebox', $icebox);
		$this->assertEquals('yellow', $icebox->color);
		$this->assertTrue($icebox->repaired);
		$this->assertEquals(0, $icebox->capacity);
		$this->assertEquals([], $icebox->food);

		$this->markTestIncomplete('Test cache - create one more and check count of calling getById.');
	}
}
