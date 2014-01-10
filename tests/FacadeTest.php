<?php

namespace Shelter\Tests\Facade;

use Shelter;

class PersonFacade extends Shelter\Facade
{
}

class Test extends Shelter\Tests\TestCase
{

	/** @var PersonFacade */
	private $facade;

	/** @var \Mockery\Mock */
	private $accessor;


	protected function setUp()
	{
		parent::setUp();

		$this->accessor = \Mockery::mock('Shelter\IAccessor');
		$this->facade = new PersonFacade($this->accessor);
	}

	public function testGetById()
	{
		$this->accessor
			->shouldReceive('getById')
			->once()
			->with('Shelter\Tests\Person', 1);

		$this->facade->getById(1);
	}


	public function testGetByRestrictions()
	{
		$this->accessor
			->shouldReceive('getByRestrictions')
			->once()
			->with('Shelter\Tests\Person', \Mockery::type('Shelter\IRestrictor'));

		$restrictor = \Mockery::mock('Shelter\IRestrictor');
		$this->facade->getByRestrictions($restrictor);
	}
}
