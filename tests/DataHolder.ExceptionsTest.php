<?php

namespace Shelter\Tests\DataHolder;

use Shelter;

class ExceptionsTest extends Shelter\Tests\TestCase
{

	/** @var \Mockery\Mock */
	private $suggestor;

	/** @var \Mockery\Mock */
	private $paramMap;

	/** @var Shelter\DataHolder */
	private $holder;


	public function setUp()
	{
		parent::setUp();
		$this->paramMap = \Mockery::mock('Shelter\IParamMap');
		$this->suggestor = \Mockery::mock('Shelter\ISuggestor')
			->shouldReceive('getParamMap')
			->andReturn($this->paramMap);
	}


	/**
	 * @expectedException Shelter\Exception
	 */
	public function testIsDataOnTypeNotSeparated()
	{
		$holder = new Shelter\DataHolder($this->suggestor);

		$this->paramMap
			->shouldReceive('isSeparatedByType')
			->once()
			->andReturn(FALSE);

		$holder->isDataOnType('iWantType');
	}


	/**
	 * @expectedException Shelter\Exception
	 */
	public function testIsDataOnTypeUnknownType()
	{
		$holder = new Shelter\DataHolder($this->suggestor);

		$this->paramMap
			->shouldReceive('isSeparatedByType')
			->once()
			->andReturn(TRUE)
			->getMock()
			->shouldReceive('hasType')
			->once()
			->with('unknown')
			->andReturn(FALSE);

		$holder->isDataOnType('unknown');
	}


	/**
	 * @expectedException Shelter\Exception
	 */
	public function testGetParamsByTypeNotSeparated()
	{
		$holder = new Shelter\DataHolder($this->suggestor);

		$this->paramMap
			->shouldReceive('isSeparatedByType')
			->once()
			->andReturn(FALSE);

		$holder->getParams('unknown');
	}


	/**
	 * @expectedException Shelter\Exception
	 */
	public function testGetParamsUnknownType()
	{
		$holder = new Shelter\DataHolder($this->suggestor);

		$this->paramMap
			->shouldReceive('isSeparatedByType')
			->once()
			->andReturn(TRUE)
			->getMock()
			->shouldReceive('hasType')
			->once()
			->andReturn(FALSE);

		$holder->getParams('unknown');
	}


	/**
	 * @expectedException Shelter\Exception
	 * @todo tady to má brát z suggestoru ne z parammapy
	 */
	public function testSetParamsNonexistent()
	{
		$holder = new Shelter\DataHolder($this->suggestor);

		$this->suggestor
			->shouldReceive()

		$this->paramMap
			->shouldReceive('getMap')
			->once()
			->with(NULL)
			->andReturn(array('name' => NULL));

		$holder->setParams(array('age' => 25));
	}


	/**
	 * @expectedException Shelter\Exception
	 */
	public function testSetParamsLikeType()
	{
		$holder = new Shelter\DataHolder($this->suggestor);

		$this->paramMap
			->shouldReceive('getMap')
			->once()
			->with(NULL)
			->andReturn(array('skills' => array('power' => NULL)));

		$holder->setParams(array('skills' => 'intelligence'));
	}


	/**
	 * @expectedException Shelter\Exception
	 */
	public function testeGetUnknownDescendant()
	{
		$holder = new Shelter\DataHolder($this->suggestor);

		$holder->getDescendant('World\Animal', 'pet_id');
	}
}
