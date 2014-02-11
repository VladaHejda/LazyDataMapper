<?php

namespace Shelter\Tests\Get;

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

		$this->assertNull($facade->getById(99));

		$icebox = $facade->getById(2);

		$this->assertEquals('black', $icebox->color);
		$this->assertEquals('black', $icebox->color);
		$this->assertEquals(['beef steak', 'milk', 'egg'], $icebox->food);
		$this->assertTrue(FALSE === $icebox->freezer);
		$this->assertTrue(45 === $icebox->capacity);
		$this->assertTrue(45 === $icebox->capacity());
		$this->assertTrue(45 === $icebox->capacity('l'));
		$this->assertTrue(0 === $icebox->freezerCapacity);
		$this->assertEquals('Black icebox, 45 l.', $icebox->description);
		$this->assertEquals('<p>Black icebox, 45 l.</p>', $icebox->taggedDescription);
		$this->assertTrue($icebox->repaired);

		return $icebox;
	}


	/**
	 * @depends testGet
	 */
	public function testUnobtainable(Tests\Icebox $icebox)
	{
		// undeclared
		$this->assertException(
			function() use ($icebox) { $icebox->undeclared; },
			'Shelter\EntityException', Shelter\EntityException::READ_UNDECLARED
		);

		// private
		$this->assertException(
			function() use ($icebox) { $icebox->repairs; },
			'Shelter\EntityException', Shelter\EntityException::READ_UNDECLARED
		);
	}
}
