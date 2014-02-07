<?php

namespace Shelter\Tests\GetContainer;

use Shelter,
	Shelter\Tests;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Icebox.php';

class Test extends Shelter\Tests\TestCase
{

	public function testGet()
	{
		$requestKey = new Shelter\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new Shelter\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new Shelter\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\IceboxFacade($accessor, $serviceAccessor);

		$iceboxes = $facade->getByRestrictions([2, 5, 8]);

		$this->assertInstanceOf('Shelter\Tests\Icebox', $iceboxes[0]);
		$this->assertInstanceOf('Shelter\Tests\Icebox', $iceboxes[1]);
		$this->assertInstanceOf('Shelter\Tests\Icebox', $iceboxes[2]);

		$this->assertEquals('black', $iceboxes[0]->color);
		$this->assertEquals('silver', $iceboxes[1]->color);
		$this->assertEquals('blue', $iceboxes[2]->color);

		$expected = [
			2 => ['beef steak', 'milk', 'egg'],
			5 => [],
			8 => ['jam'],
		];

		foreach ($iceboxes as $icebox) {
			$this->assertInstanceOf('Shelter\Tests\Icebox', $icebox);
			$this->assertEquals($expected[$icebox->getId()], $icebox->food);
		}
	}
}
