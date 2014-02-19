<?php

namespace LazyDataMapper\Tests\Get;

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

		$this->assertTrue($icebox->isReadOnly('food'));
		$this->assertFalse($icebox->isReadOnly('color'));

		$this->assertTrue(isset($icebox->color));
		$this->assertTrue(isset($icebox->repaired));
		$this->assertFalse(isset($icebox->undeclared));
		$this->assertFalse(isset($icebox->repairs));

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
			'LazyDataMapper\EntityException', LazyDataMapper\EntityException::READ_UNDECLARED
		);

		// private
		$this->assertException(
			function() use ($icebox) { $icebox->repairs; },
			'LazyDataMapper\EntityException', LazyDataMapper\EntityException::READ_UNDECLARED
		);
	}
}
