<?php

namespace Shelter\Tests\DataHolder;

use Shelter;

class GetSetTest extends Shelter\Tests\TestCase
{

	/** @var \Mockery\Mock */
	private $suggestor;

	/** @var \Mockery\Mock */
	private $paramMap;


	public function setUp()
	{
		parent::setUp();
		$this->paramMap = \Mockery::mock('Shelter\IParamMap');
		$this->suggestor = \Mockery::mock('Shelter\ISuggestor')
			->shouldReceive('getParamMap')
			->andReturn($this->paramMap)
			->getMock();
	}


	public function testGetParamsNotSet()
	{
		$holder = new Shelter\DataHolder($this->suggestor);
		$this->assertEquals(array(), $holder->getParams());
	}


	public function testSetParams()
	{
		$holder = new Shelter\DataHolder($this->suggestor);

		$this->suggestor
			->shouldReceive('getParamNames')
			->once()
			->andReturn(array('name'));

		$params = array('name' => 'Marry');
		$holder->setParams($params);
		$this->assertEquals($params, $holder->getParams());
	}


	public function testSetParamsTyped()
	{
		$holder = new Shelter\DataHolder($this->suggestor);

		$this->paramMap
			->shouldReceive('getMap')
			->once()
			->with('personal')
			->andReturn(array('name' => NULL, 'age' => NULL))
			->getMock()
			->shouldReceive('getMap')
			->once()
			->with('skills')
			->andReturn(array('power' => NULL, 'intelligence' => NULL));

		$this->suggestor
			->shouldReceive('getParamNames')
			->once()
			->andReturn(array('name', 'age', 'power'));

		$params = array('name' => 'Marry', 'power' => 250);
		$holder->setParams($params);
		$this->assertEquals($params, $holder->getParams());
		$this->assertEquals(array('name' => 'Marry'), $holder->getParams('personal'));
		$this->assertEquals(array('power' => 250), $holder->getParams('skills'));
	}
}
