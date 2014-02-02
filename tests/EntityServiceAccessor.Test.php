<?php

namespace Shelter\Tests\EntityServiceAccessor;

use Shelter;

require_once __DIR__.'/prepared/Facade.php';

class Test extends Shelter\Tests\TestCase
{

	/** @var Shelter\EntityServiceAccessor */
	private $serviceAccessor;


	protected function setUp()
	{
		parent::setUp();
		$this->serviceAccessor = \Mockery::mock('Shelter\EntityServiceAccessor[]');
	}


	public function testGetEntityContainerClass()
	{
		$this->assertEquals('worlds', $this->serviceAccessor->getEntityContainerClass('world'));
		$this->assertEquals('stories', $this->serviceAccessor->getEntityContainerClass('story'));
	}


	public function testGetEntityClass()
	{
		$facade = \Mockery::mock('Shelter\Tests\Facade\EmptyFacade');
		$this->assertEquals('Shelter\Tests\Facade\Empty', $this->serviceAccessor->getEntityClass($facade));
	}
}
