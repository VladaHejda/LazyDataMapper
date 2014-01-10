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
		$this->holder = new Shelter\DataHolder($this->suggestor);
	}


	/**
	 * @expectedException Shelter\Exception
	 */
	public function testIsDataOnTypeNotSeparated()
	{
		$this->paramMap
			->shouldReceive('isSeparatedByType')
			->once()
			->andReturn(FALSE);

		$this->holder->isDataOnType('iWantType');
	}


	/**
	 * @expectedException Shelter\Exception
	 */
	public function testIsDataOnTypeUnknownType()
	{
		$this->paramMap
			->shouldReceive('isSeparatedByType')
			->once()
			->andReturn(TRUE)
			->getMock()
			->shouldReceive('hasType')
			->once()
			->with('unknown')
			->andReturn(FALSE);

		$this->holder->isDataOnType('unknown');
	}


	/**
	 * @expectedException Shelter\Exception
	 */
	public function testGetParamsByTypeNotSeparated()
	{
		$this->paramMap
			->shouldReceive('isSeparatedByType')
			->once()
			->andReturn(FALSE);

		$this->holder->getParams('unknown');
	}


	/**
	 * @expectedException Shelter\Exception
	 */
	public function testGetParamsUnknownType()
	{
		$this->paramMap
			->shouldReceive('isSeparatedByType')
			->once()
			->andReturn(TRUE)
			->getMock()
			->shouldReceive('hasType')
			->once()
			->andReturn(FALSE);

		$this->holder->getParams('unknown');
	}


	/**
	 * @expectedException Shelter\Exception
	 * @todo tady to má brát z suggestoru ne z parammapy
	 */
	public function testSetParamsNonexistent()
	{
		$this->suggestor
			->shouldReceive()

		$this->paramMap
			->shouldReceive('getMap')
			->once()
			->with(NULL)
			->andReturn(array('name' => NULL));

		$this->holder->setParams(array('age' => 25));
	}


	/**
	 * @expectedException Shelter\Exception
	 */
	public function testSetParamsLikeType()
	{
		$this->paramMap
			->shouldReceive('getMap')
			->once()
			->with(NULL)
			->andReturn(array('skills' => array('power' => NULL)));

		$this->holder->setParams(array('skills' => 'intelligence'));
	}


	/**
	 * @expectedException Shelter\Exception
	 */
	public function testeGetUnknownDescendant()
	{
		$this->holder->getDescendant('World\Animal', 'pet_id');
	}
}
