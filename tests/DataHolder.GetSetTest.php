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
			->andReturn(array('power' => NULL, 'intelligence' => NULL))
			->getMock()
			->shouldReceive('getMap')
			->once()
			->with('personal', FALSE)
			->andReturn(array('name', 'age'))
			->getMock()
			->shouldReceive('getMap')
			->with('results', FALSE)
			->andReturn(array('maturity'));

		$this->suggestor
			->shouldReceive('getParamNames')
			->once()
			->andReturn(array('name', 'age', 'power'));

		$params = array('name' => 'Marry', 'power' => 250);
		$holder->setParams($params);
		$this->assertEquals($params, $holder->getParams());
		$this->assertEquals(array('name' => 'Marry'), $holder->getParams('personal'));
		$this->assertEquals(array('power' => 250), $holder->getParams('skills'));
		$this->assertTrue($holder->isDataOnType('personal'));
		$this->assertFalse($holder->isDataOnType('results'));
	}


	public function testSetParamsInContainer()
	{
		$holder = new Shelter\DataHolder($this->suggestor, array(2, 5));

		$this->suggestor
			->shouldReceive('getParamNames')
			->once()
			->andReturn(array('name', 'skill'));

		$params = array(
			2 => array('name' => 'Jack', 'skill' => 'sexy'),
			5 => array('name' => 'Christie', 'skill' => 'nice'),
		);

		$paramsSkillOnly = array(
			2 => array('skill' => 'sexy'),
			5 => array('skill' => 'nice'),
		);

		$this->paramMap
			->shouldReceive('getMap')
			->once()
			->with('extra')
			->andReturn(array('skill' => NULL, 'power' => NULL))
			->getMock()
			->shouldReceive('getMap')
			->once()
			->with('extra', FALSE)
			->andReturn(array('skill', 'power'))
			->getMock()
			->shouldReceive('getMap')
			->once()
			->with('results', FALSE)
			->andReturn(array('maturity'));

		$holder->setParams($params);
		$this->assertEquals($params, $holder->getParams());
		$this->assertEquals($paramsSkillOnly, $holder->getParams('extra'));
		$this->assertTrue($holder->isDataOnType('extra'));
		$this->assertFalse($holder->isDataOnType('results'));
	}


	public function testSetParamsInContainerGradually()
	{
		$holder = new Shelter\DataHolder($this->suggestor, array(2, 5, 6));

		$this->suggestor
			->shouldReceive('getParamNames')
			->twice()
			->andReturn(array('name', 'skill'));

		$params = array(
			2 => array('name' => 'Jack', 'skill' => 'sexy'),
			5 => array('name' => 'Christie', 'skill' => 'nice'),
		);

		$holder->setParams(array(2 => $params[2]));
		$holder->setParams(array(5 => $params[5]));
		$this->assertEquals($params, $holder->getParams());
	}
}
