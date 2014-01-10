<?php

namespace Shelter\Tests\DataHolder;

use Shelter;

class GetSetTest extends Shelter\Tests\TestCase
{

	/** @var \Mockery\Mock */
	private $suggestor;


	public function setUp()
	{
		parent::setUp();
		$this->suggestor = \Mockery::mock('Shelter\ISuggestor');
	}


	public function testSetParams()
	{
		$holder = new Shelter\DataHolder($this->suggestor);

		$this->assertEquals(array(), $holder->getParams());
	}
}
