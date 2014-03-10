<?php

namespace LazyDataMapper\Tests\EntityServiceAccessor;

use LazyDataMapper;

require_once __DIR__ . '/prepared/Facade.php';

class Test extends LazyDataMapper\Tests\TestCase
{

	/** @var LazyDataMapper\EntityServiceAccessor */
	private $serviceAccessor;


	protected function setUp()
	{
		parent::setUp();
		$this->serviceAccessor = \Mockery::mock('LazyDataMapper\EntityServiceAccessor[]');
	}


	public function testGetEntityContainerClass()
	{
		$this->assertEquals('worlds', $this->serviceAccessor->getEntityContainerClass('world'));
		$this->assertEquals('stories', $this->serviceAccessor->getEntityContainerClass('story'));
	}


	public function testGetEntityClass()
	{
		$accessor = new LazyDataMapper\Accessor(\Mockery::mock('LazyDataMapper\ISuggestorCache'), \Mockery::mock('LazyDataMapper\IEntityServiceAccessor'));
		$facade = new LazyDataMapper\Tests\Facade\EmptyFacade($accessor);
		$this->assertEquals('LazyDataMapper\Tests\Facade\Empty', $this->serviceAccessor->getEntityClass($facade));
	}
}
