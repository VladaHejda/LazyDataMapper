<?php

namespace Shelter\Tests\Set;

use Shelter,
	Shelter\Tests,
	Shelter\Tests\IceboxMapper;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Icebox.php';

class Test extends Shelter\Tests\AcceptanceTestCase
{

	/** @var Tests\IceboxFacade */
	private static $facade;


	public function testSet()
	{
		$requestKey = new Shelter\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new Shelter\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new Shelter\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\IceboxFacade($accessor, $serviceAccessor);
		self::$facade = $facade;

		$icebox = $facade->getById(4);

		$icebox->capacity = 30;
		$this->assertEquals(['capacity'], IceboxMapper::$lastSuggestor->getParamNames());

		$this->assertEquals('white', $icebox->color);
		$icebox->color = 'yellow';
		$this->assertEquals(30, $icebox->capacity);
		$this->assertEquals('yellow', $icebox->color);
		$this->assertTrue($icebox->isChanged());
		$this->assertTrue($icebox->isChanged('color'));
		$this->assertFalse($icebox->isChanged('food'));
		$this->assertEquals('white', $icebox->getOriginal('color'));
		$this->assertEquals(['color' => 'yellow', 'capacity' => 30], $icebox->getChanges());

		$this->assertEquals(2, IceboxMapper::$calledGetById);

		return $icebox;
	}


	/**
	 * @depends testSet
	 */
	public function testUnset(Tests\Icebox $icebox)
	{
		unset($icebox->capacity);
		$this->assertEquals(0, $icebox->capacity);

		return $icebox;
	}


	/**
	 * @depends testUnset
	 */
	public function testReset(Tests\Icebox $icebox)
	{
		$icebox->reset('capacity');
		$icebox->reset('color');
		$this->assertEquals('white', $icebox->color);
		$this->assertFalse($icebox->isChanged());
		$this->assertFalse($icebox->isChanged('color'));
		$this->assertEquals('white', $icebox->getOriginal('color'));
		$this->assertEquals([], $icebox->getChanges());

		$icebox->color = 'orange';
		$icebox->capacity = 25;
		$icebox->reset('capacity');
		$this->assertTrue($icebox->isChanged());

		$icebox->capacity = 30;
		$icebox->reset();
		$this->assertFalse($icebox->isChanged());
		$this->assertFalse($icebox->isChanged('color'));
		$this->assertFalse($icebox->isChanged('capacity'));

		$this->assertEquals(0, IceboxMapper::$calledGetById);

		return $icebox;
	}


	/**
	 * @depends testReset
	 */
	public function testUnwrapper(Tests\Icebox $icebox)
	{
		$this->assertFalse($icebox->freezer);
		$this->assertEquals(0, $icebox->freezerCapacity);
		$icebox->freezerCapacity = 4;
		$this->assertTrue($icebox->freezer);
		$this->assertEquals(4, $icebox->freezerCapacity);

		$this->assertEquals(1, IceboxMapper::$calledGetById);

		return $icebox;
	}


	/**
	 * @depends testUnwrapper
	 */
	public function testSetPrivate(Tests\Icebox $icebox)
	{
		$icebox->reset();
		$this->assertEquals(1, $icebox->addRepair());

		$this->assertEquals(1, IceboxMapper::$calledGetById);

		$this->assertTrue($icebox->isChanged());
		$this->assertTrue($icebox->isChanged('repairs'));
		$this->assertEquals(0, $icebox->getOriginal('repairs'));
		$this->assertEquals(['repairs' => 1], $icebox->getChanges());

		return $icebox;
	}


	/**
	 * @depends testSetPrivate
	 */
	public function testSetReadonly(Tests\Icebox $icebox)
	{
		$icebox->addFood('cheese');
		$this->assertEquals(['egg', 'butter', 'cheese'], $icebox->food);

		return $icebox;
	}


	/**
	 * @depends testSetReadonly
	 */
	public function testSave(Tests\Icebox $icebox)
	{
		$icebox->reset();
		$icebox->color = 'brown';
		$icebox->save();

		$this->assertEquals(['color' => 'brown'], IceboxMapper::$lastHolder->getParams());

		$this->assertEquals('brown', $icebox->color);
		$this->assertEquals(20, $icebox->capacity);
		$this->assertFalse($icebox->isChanged());
		$this->assertFalse($icebox->isChanged('color'));
		$this->assertFalse($icebox->isChanged('capacity'));
		$this->assertEquals('brown', $icebox->getOriginal('color'));
		$this->assertEquals([], $icebox->getChanges());

		$icebox->reset();
		$this->assertEquals('brown', $icebox->color);
	}


	/**
	 * @depends testSave
	 */
	public function testSuccessfullySaved()
	{
		$icebox = self::$facade->getById(4);
		$this->assertEquals('brown', $icebox->color);

		$this->assertEquals(1, IceboxMapper::$calledGetById);

		return $icebox;
	}


	/**
	 * @depends testSet
	 */
	public function testImmutable(Tests\Icebox $icebox)
	{
		// undeclared
		$this->assertException(
			function() use ($icebox) { $icebox->undeclared = ''; },
			'Shelter\EntityException', Shelter\EntityException::WRITE_UNDECLARED
		);

		// private
		$this->assertException(
			function() use ($icebox) { $icebox->repairs = ''; },
			'Shelter\EntityException', Shelter\EntityException::WRITE_UNDECLARED
		);

		// readonly
		$this->assertException(
			function() use ($icebox) { $icebox->food = ''; },
			'Shelter\EntityException', Shelter\EntityException::WRITE_READONLY
		);
	}
}
