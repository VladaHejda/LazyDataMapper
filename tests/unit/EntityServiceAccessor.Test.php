<?php

namespace LazyDataMapper\Tests\EntityServiceAccessor;

use LazyDataMapper;

require_once __DIR__ . '/prepared/Empty.php';

class Test extends LazyDataMapper\Tests\TestCase
{

	/** @var LazyDataMapper\EntityServiceAccessor */
	private $serviceAccessor;


	protected function setUp()
	{
		parent::setUp();
		$this->serviceAccessor = \Mockery::mock('LazyDataMapper\EntityServiceAccessor[createParamMap, createMapper, createChecker]')
			->shouldAllowMockingProtectedMethods();
	}


	protected function prepareAccessor()
	{
		$suggestorCache = \Mockery::mock('LazyDataMapper\SuggestorCache');
		return new LazyDataMapper\Accessor($suggestorCache, $this->serviceAccessor);
	}


	public function testGetEntityCollectionClass()
	{
		$this->assertEquals('LazyDataMapper\Tests\Worlds', $this->serviceAccessor->getEntityCollectionClass('LazyDataMapper\Tests\World'));
		$this->assertEquals('LazyDataMapper\Tests\Stories', $this->serviceAccessor->getEntityCollectionClass('LazyDataMapper\Tests\Story'));
	}


	public function testGetEntityClass()
	{
		$facade = new LazyDataMapper\Tests\SomeFacade($this->prepareAccessor(), $this->serviceAccessor);
		$this->assertEquals('LazyDataMapper\Tests\Some', $this->serviceAccessor->getEntityClass($facade));
	}


	public function testGetEntityClassNamespaced()
	{
		$facade = new LazyDataMapper\Tests\Some\Facade($this->prepareAccessor(), $this->serviceAccessor);
		$this->assertEquals('LazyDataMapper\Tests\Some', $this->serviceAccessor->getEntityClass($facade));
	}


	public function testGetParamMap()
	{
		$this->serviceAccessor->shouldReceive('createParamMap')
			->with('LazyDataMapper\Tests\Some\ParamMap');
		$this->serviceAccessor->getParamMap('LazyDataMapper\Tests\Some');
	}


	public function testGetMapper()
	{
		$this->serviceAccessor->shouldReceive('createMapper')
			->with('LazyDataMapper\Tests\Some\Mapper');
		$this->serviceAccessor->getMapper('LazyDataMapper\Tests\Some');
	}


	public function testGetChecker()
	{
		$this->serviceAccessor->shouldReceive('createChecker')
			->with('LazyDataMapper\Tests\Some\Checker');
		$this->serviceAccessor->getChecker('LazyDataMapper\Tests\Some');
	}
}
