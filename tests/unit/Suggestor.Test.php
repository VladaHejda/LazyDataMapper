<?php

namespace LazyDataMapper\Tests\Suggestor;

use LazyDataMapper,
	LazyDataMapper\Suggestor;

class Test extends LazyDataMapper\Tests\TestCase
{

	/** @var \Mockery\Mock */
	private $paramMap;

	/** @var \Mockery\Mock */
	private $suggestorCache;

	/** @var LazyDataMapper\IIdentifier */
	private $identifier;

	protected function setUp()
	{
		parent::setUp();

		$this->paramMap = \Mockery::mock('LazyDataMapper\ParamMap')
			->shouldReceive('hasParam')
			->with(\Mockery::anyOf('name', 'age'))
			->twice()
			->andReturn(TRUE)
			->getMock();
		$this->suggestorCache = \Mockery::mock('LazyDataMapper\SuggestorCache');
		$this->identifier = \Mockery::mock('LazyDataMapper\IIdentifier');
	}


	public function testBase()
	{
		$suggestor = new Suggestor($this->paramMap, $this->suggestorCache, ['name', 'age'], FALSE, $this->identifier);

		$this->assertSame($this->identifier, $suggestor->getIdentifier());
		$this->assertSame($this->paramMap, $suggestor->getParamMap());
		$this->assertFalse($suggestor->isCollection());
		$this->assertFalse($suggestor->hasChildren());
		$this->assertEquals(['name', 'age'], $suggestor->getSuggestions());

		$this->paramMap
			->shouldReceive('getMap')
			->with('whatever', FALSE)
			->andThrow('LazyDataMapper\Exception');

		$this->assertException(function() use ($suggestor) {
			$suggestor->isSuggestedGroup('whatever');
		}, 'LazyDataMapper\Exception');
	}


	public function testCollection()
	{
		$suggestor = new Suggestor($this->paramMap, $this->suggestorCache, ['name', 'age'], TRUE);

		$this->assertTrue($suggestor->isCollection());
		$this->assertFalse($suggestor->hasChildren());
	}


	public function testWithChild()
	{
		$this->suggestorCache
			->shouldReceive('getCached')
			->once()
			->andReturn(\Mockery::mock('LazyDataMapper\Suggestor'));

		$children = [
			'car' => ['Car', FALSE, $this->identifier]
		];
		$suggestor = new Suggestor($this->paramMap, $this->suggestorCache, ['name', 'age'], FALSE, NULL, $children);

		$this->assertFalse($suggestor->isCollection());
		$this->assertTrue($suggestor->hasChildren());
	}


	public function testGroupedParamMap()
	{
		$map = [
			'personal' => ['name', 'age'],
			'skill' => ['power'],
		];

		$this->paramMap
			->shouldReceive('getMap')
			->with('personal', FALSE)
			->once()
			->andReturn($map['personal'])
		->getMock()
			->shouldReceive('getMap')
			->with('skill', FALSE)
			->once()
			->andReturn($map['skill']);

		$suggestor = new Suggestor($this->paramMap, $this->suggestorCache, ['name', 'age']);

		$this->assertTrue($suggestor->isSuggestedGroup('personal'));
		$this->assertFalse($suggestor->isSuggestedGroup('skill'));
	}


	/**
	 * @expectedException LazyDataMapper\Exception
	 */
	public function testWrongSuggestions()
	{
		$this->paramMap
			->shouldReceive('hasParam')
			->with('unknown')
			->once()
			->andReturn(FALSE);

		new Suggestor($this->paramMap, $this->suggestorCache, ['name', 'age', 'unknown']);
	}
}
