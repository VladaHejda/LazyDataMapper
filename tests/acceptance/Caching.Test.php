<?php

namespace Shelter\Tests\Caching;

use Shelter,
	Shelter\Tests,
	Shelter\Tests\IceboxMapper;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Icebox.php';

class Test extends Shelter\Tests\TestCase
{

	public function testFirstGet()
	{
		$requestKey = new Shelter\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\IceboxServiceAccessor;
		$suggestorCache = new Shelter\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new Shelter\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\IceboxFacade($accessor, $serviceAccessor);

		$icebox = $facade->getById(2);

		$this->assertEquals('black', $icebox->color);
		$this->assertEquals(['color'], IceboxMapper::$lastSuggestor->getParamNames());
		$this->assertEquals(['beef steak', 'milk', 'egg'], $icebox->food);
		$this->assertEquals(['food'], IceboxMapper::$lastSuggestor->getParamNames());
		$this->assertEquals(2, IceboxMapper::$calledCounter['getById']);

		return $cache;
	}


	/**
	 * @depends testFirstGet
	 */
	public function testSecondGet($cache)
	{
		$this->markTestIncomplete(
			'Identifier counts entity top call across these two tests.
			 I need to execute this one like it is independent.'
		);

		$requestKey = new Shelter\RequestKey;
		$serviceAccessor = new Tests\IceboxServiceAccessor;
		$suggestorCache = new Shelter\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new Shelter\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\IceboxFacade($accessor, $serviceAccessor);

		$icebox = $facade->getById(4);

		$this->assertEquals('white', $icebox->color);
		$this->assertEquals(['color', 'food'], IceboxMapper::$lastSuggestor->getParamNames());
		$this->assertEquals(['egg', 'butter'], $icebox->food);
		$this->assertEquals(1, IceboxMapper::$calledCounter['getById']);
	}
}
