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
}
