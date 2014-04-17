<?php

namespace LazyDataMapper\Tests\DataHolder;

use LazyDataMapper,
	LazyDataMapper\DataHolder;

class Test extends LazyDataMapper\Tests\TestCase
{

	public function testBase()
	{
		$paramMap = \Mockery::mock('LazyDataMapper\ParamMap')
			->shouldReceive('getMap')
			->with('whatever', FALSE)
			->once()
			->andThrow('LazyDataMapper\Exception')
		->getMock()
			->shouldReceive('getMap')
			->with('whatever')
			->once()
			->andThrow('LazyDataMapper\Exception')
		->getMock();

		$suggestor = \Mockery::mock('LazyDataMapper\Suggestor')
			->shouldReceive('isCollection')
			->andReturn(FALSE)
		->getMock()
			->shouldReceive('getSuggestions')
			->twice()
			->andReturn(['name', 'age'])
		->getMock()
			->shouldReceive('getParamMap')
			->twice()
			->andReturn($paramMap)
		->getMock();

		$dataHolder = new DataHolder($suggestor);

		$this->assertSame($suggestor, $dataHolder->getSuggestor());

		$data = ['name' => 'George', 'age' => 25];
		$dataHolder->setParams($data);
		$this->assertEquals($data, $dataHolder->getParams());

		$dataHolder->setParams($data + ['extra' => 'whatever']);
		$this->assertEquals($data, $dataHolder->getParams());

		$this->assertException(function () use ($dataHolder){
			$dataHolder->isDataInGroup('whatever');
		}, 'LazyDataMapper\Exception');

		$this->assertException(function () use ($dataHolder){
			$dataHolder->getParams('whatever');
		}, 'LazyDataMapper\Exception');
	}


	public function testCollection()
	{
		$suggestor = \Mockery::mock('LazyDataMapper\Suggestor')
			->shouldReceive('isCollection')
			->andReturn(TRUE)
		->getMock()
			->shouldReceive('getSuggestions')
			->times(3)
			->andReturn(['name', 'age'])
		->getMock()
		;

		$dataHolder = new DataHolder($suggestor);

		$data = [
			3 => ['name' => 'George', 'age' => 25],
			7 => ['name' => 'John', 'age' => 17],
		];

		$dataHolder->setParams($data);
		$this->assertEquals($data, $dataHolder->getParams());

		$dataHolder->setParams([3 => $data[3]]);
		$this->assertEquals($data, $dataHolder->getParams());

		$modifiedData = $data;
		$modifiedData[3]['extra'] = 'whatever';
		$dataHolder->setParams($modifiedData);
		$this->assertEquals($data, $dataHolder->getParams());
	}


	public function testGroupedMap()
	{
		$map = [
			'personal' => ['name', 'age'],
			'skill' => ['power'],
		];

		$paramMap = \Mockery::mock('LazyDataMapper\ParamMap')
			->shouldReceive('getMap')
			->with('personal', FALSE)
			->times(3)
			->andReturn($map['personal'])
		->getMock()
			->shouldReceive('getMap')
			->with('personal')
			->once()
			->andReturn(['name' => NULL, 'age' => NULL])
		->getMock()
			->shouldReceive('getMap')
			->with('skill', FALSE)
			->times(3)
			->andReturn($map['skill'])
		->getMock()
			->shouldReceive('getMap')
			->with('skill')
			->once()
			->andReturn(['power' => NULL])
		->getMock()
			->shouldReceive('getMap')
			->with('unknown', FALSE)
			->once()
			->andThrow('LazyDataMapper\Exception')
		->getMock();

		$suggestor = \Mockery::mock('LazyDataMapper\Suggestor')
			->shouldReceive('isCollection')
			->andReturn(FALSE)
		->getMock()
			->shouldReceive('getParamMap')
			->andReturn($paramMap)
		->getMock()
			->shouldReceive('getSuggestions')
			->with()
			->times(2)
			->andReturn(['name', 'power'])
		->getMock();

		$dataHolder = new DataHolder($suggestor);

		$this->assertFalse($dataHolder->isDataInGroup('personal'));
		$this->assertFalse($dataHolder->isDataInGroup('skill'));
		$dataHolder->setParams(['power' => 120]);
		$this->assertFalse($dataHolder->isDataInGroup('personal'));
		$this->assertTrue($dataHolder->isDataInGroup('skill'));
		$dataHolder->setParams(['name' => 'John', 'power' => 300]);
		$this->assertTrue($dataHolder->isDataInGroup('personal'));
		$this->assertTrue($dataHolder->isDataInGroup('skill'));

		$this->assertEquals(['name' => 'John', 'power' => 300], $dataHolder->getParams());
		$this->assertEquals(['name' => 'John'], $dataHolder->getParams('personal'));
		$this->assertEquals(['power' => 300], $dataHolder->getParams('skill'));

		$this->assertException(function () use ($dataHolder) {
			$dataHolder->isDataInGroup('unknown');
		}, 'LazyDataMapper\Exception');
	}


	public function testCollectionGroupedMap()
	{
		$map = [
			'personal' => ['name', 'age'],
			'skill' => ['power'],
		];

		$paramMap = \Mockery::mock('LazyDataMapper\ParamMap')
			->shouldReceive('getMap')
			->with('personal', FALSE)
			->times(3)
			->andReturn($map['personal'])
		->getMock()
			->shouldReceive('getMap')
			->with('personal')
			->once()
			->andReturn(['name' => NULL, 'age' => NULL])
		->getMock()
			->shouldReceive('getMap')
			->with('skill', FALSE)
			->times(3)
			->andReturn($map['skill'])
		->getMock()
			->shouldReceive('getMap')
			->with('skill')
			->once()
			->andReturn(['power' => NULL])
		->getMock();

		$suggestor = \Mockery::mock('LazyDataMapper\Suggestor')
			->shouldReceive('isCollection')
			->andReturn(TRUE)
		->getMock()
			->shouldReceive('getParamMap')
			->andReturn($paramMap)
		->getMock()
			->shouldReceive('getSuggestions')
			->with()
			->times(4)
			->andReturn(['name', 'power'])
		->getMock();

		$dataHolder = new DataHolder($suggestor);

		$this->assertFalse($dataHolder->isDataInGroup('personal'));
		$this->assertFalse($dataHolder->isDataInGroup('skill'));

		$data = [
			2 => ['name' => 'George', 'power' => 225],
			9 => ['name' => 'John', 'power' => 280],
		];

		$this->assertException(function () use ($dataHolder, $data) {
			$dataHolder->setParams($data[2]);
		}, 'LazyDataMapper\Exception');

		$dataHolder->setParams([2 => ['power' => 225]]);

		$this->assertFalse($dataHolder->isDataInGroup('personal'));
		$this->assertTrue($dataHolder->isDataInGroup('skill'));

		$dataHolder->setParams([2 => $data[2]]);
		$dataHolder->setParams([9 => $data[9]]);

		$this->assertTrue($dataHolder->isDataInGroup('personal'));
		$this->assertTrue($dataHolder->isDataInGroup('skill'));

		$this->assertEquals($data, $dataHolder->getParams());
		$dataPersonal = $dataSkill = $data;
		unset($dataPersonal[2]['power'], $dataPersonal[9]['power']);
		unset($dataSkill[2]['name'], $dataSkill[9]['name']);
		$this->assertEquals($dataPersonal, $dataHolder->getParams('personal'));
		$this->assertEquals($dataSkill, $dataHolder->getParams('skill'));
	}
}
