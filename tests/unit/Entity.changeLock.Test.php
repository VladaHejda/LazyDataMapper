<?php

namespace LazyDataMapper\Tests\Entity;

use LazyDataMapper;

class Car extends LazyDataMapper\Entity
{}

class ChangeLockTest extends LazyDataMapper\Tests\TestCase
{

	/** @var Car */
	private $entity;


	protected function setUp()
	{
		$this->markTestIncomplete("This is meanwhile experimental.");

		parent::setUp();

		$accessor = new LazyDataMapper\Accessor(\Mockery::mock('LazyDataMapper\ISuggestorCache'), \Mockery::mock('LazyDataMapper\IEntityServiceAccessor'));
		$identifier = \Mockery::mock('LazyDataMapper\IIdentifier');
		$data = [
			'name' => 'John', 'age' => 17
		];
		$this->entity = new Car(1, $data, $identifier, $accessor);
	}


	/**
	 * @expectedException LazyDataMapper\Exception
	 */
	public function testSaveLocked()
	{
		$this->entity->save();
	}


	public function testSaveUnlocked()
	{
		$this->entity->unlock();
		$this->entity->save();
	}


	public function testLockedNotChanged()
	{
		$this->entity->name = 'George';
		$this->assertEquals('George', $this->entity->name);
		$this->entity->unlock();
		$this->entity->age = 35;
		$this->entity->save();
		$this->assertEquals('George', $this->entity->name);
		$this->assertEquals(35, $this->entity->age);
		$this->entity->reset();
		$this->assertEquals('John', $this->entity->name);
		$this->assertEquals(35, $this->entity->age);
	}


	public function testLocking()
	{
		$this->entity->unlock();
		$this->entity->name = 'George';
		$this->entity->lock();
		$this->entity->age = 35;
		$this->entity->unlock();
		$this->entity->save();
		$this->entity->reset();
		$this->assertEquals('George', $this->entity->name);
		$this->assertEquals(17, $this->entity->age);
	}


	/**
	 * @expectedException LazyDataMapper\Exception
	 */
	public function testLockedAfterSave()
	{
		$this->entity->unlock();
		$this->entity->name = 'George';
		$this->entity->save();
		$this->entity->age = 35;
	}
}
