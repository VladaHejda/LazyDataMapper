<?php

namespace Shelter\Tests\Create;

use Shelter,
	Shelter\Tests,
	Shelter\Tests\IceboxMapper;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Icebox.php';

class Test extends Shelter\Tests\AcceptanceTestCase
{

	protected function createServices()
	{
		$requestKey = new Shelter\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new Shelter\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new Shelter\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\IceboxFacade($accessor, $serviceAccessor);
		return [$cache, $facade];
	}


	public function testCreate()
	{
		list(, $facade) = $this->createServices();

		$icebox = $facade->create([]);
		$this->assertInstanceOf('Shelter\Tests\Icebox', $icebox);
		$icebox = $facade->getById($icebox->getId());
		$this->assertInstanceOf('Shelter\Tests\Icebox', $icebox);

		$this->assertEquals(0, IceboxMapper::$calledGetById);
	}


	public function testCreateWithData()
	{
		list($cache, $facade) = $this->createServices();

		$icebox = $facade->create(['color' => 'yellow', 'repairs' => '6', ]);

		$this->assertInstanceOf('Shelter\Tests\Icebox', $icebox);
		$this->assertEquals('yellow', $icebox->color);
		$this->assertTrue($icebox->repaired);
		$this->assertEquals(0, $icebox->capacity);
		$this->assertEquals([], $icebox->food);

		$this->assertEquals(2, IceboxMapper::$calledGetById);
		$this->assertEquals(['capacity', 'food'], reset(reset($cache->cache)));

		return [$cache, $facade];
	}


	/**
	 * @depends testCreateWithData
	 */
	public function testCaching(array $services)
	{
		list($cache, $facade) = $services;

		$icebox = $facade->create(['color' => 'brown', 'capacity' => '37', ]);

		$this->assertEquals('brown', $icebox->color);
		$this->assertEquals(37, $icebox->capacity);
		$this->assertEquals([], $icebox->food);

		$this->assertEquals(1, IceboxMapper::$calledGetById);

		$this->assertFalse($icebox->repaired);
		$this->assertEquals(2, IceboxMapper::$calledGetById);
		$this->assertEquals(['capacity', 'food', 'repairs'], reset(reset($cache->cache)));
	}
}
