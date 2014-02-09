<?php

namespace Shelter\Tests\Set;

use Shelter,
	Shelter\Tests;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Icebox.php';

class Test extends Shelter\Tests\TestCase
{

	public function testSet()
	{
		// TODO reset static data at tearDown!
		// todo am I testing all dependencies if there is wrapper & unwrapper of fictive parameter?

		$requestKey = new Shelter\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new Shelter\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new Shelter\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\IceboxFacade($accessor, $serviceAccessor);

		$icebox = $facade->getById(4);

		$icebox->color = 'yellow';
		$this->assertEquals('yellow', $icebox->color);
		$this->assertTrue($icebox->isChanged());
		$this->assertTrue($icebox->isChanged('color'));
		$this->assertFalse($icebox->isChanged('capacity'));
		$this->assertEquals('white', $icebox->getOriginal('color'));
		$this->assertEquals(['color' => 'yellow'], $icebox->getChanges());

		return $icebox;
	}


	/**
	 * @depends testSet
	 */
	public function testReset(Tests\Icebox $icebox)
	{
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

		return $icebox;
	}


	/**
	 * @depends testReset
	 */
	public function testUnwrapper(Tests\Icebox $icebox)
	{
		$icebox->upgrade = 2;
		$this->assertEquals(40, $icebox->capacity);
		$this->assertEquals('metallic white', $icebox->color);

		return $icebox;
	}


	/**
	 * @depends testUnwrapper
	 */
	public function testSetPrivate(Tests\Icebox $icebox)
	{
		$icebox->reset();
		$this->assertEquals(1, $icebox->addRepair());
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
		$requestKey = new Shelter\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new Shelter\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new Shelter\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\IceboxFacade($accessor, $serviceAccessor);

		$icebox = $facade->getById(4);

		$this->assertEquals('brown', $icebox->color);

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

		$this->markTestIncomplete();

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
