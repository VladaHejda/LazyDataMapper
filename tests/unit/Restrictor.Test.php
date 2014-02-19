<?php

namespace Shelter\Tests\Restrictor;

use Shelter,
	Shelter\Restrictor;

class Test extends Shelter\Tests\TestCase
{

	/** @var \OpenObject */
	private $restrictor;


	protected function setUp()
	{
		parent::setUp();
		$this->restrictor =  new \OpenObject(\Mockery::mock('Shelter\Restrictor[]'));
	}


	public function testEquals()
	{
		$this->restrictor->equals('color', ['blue', 'red']);
		$this->assertEquals(['blue', 'red'], $this->restrictor->getEqual('color'));

		$this->restrictor->equals('level', 101);
		$this->assertEquals([101], $this->restrictor->getEqual('level'));

		$this->restrictor->equals('score', [10, 11]);
		$this->assertEquals([10, 11], $this->restrictor->getEqual('score'));
		$this->assertEquals(['blue', 'red'], $this->restrictor->getEqual('color'));

		$this->assertException(function() {
			$this->restrictor->notEquals('color', ['yellow', 'brown']);
		}, 'Shelter\Exception');

		$this->restrictor->equals('color', ['black'], Restrictor::UNION);
		$this->assertEquals(['blue', 'red', 'black'], $this->restrictor->getEqual('color'));

		$this->restrictor->equals('color', 'white', Restrictor::REPLACE);
		$this->assertEquals(['white'], $this->restrictor->getEqual('color'));
	}


	public function testNotEquals()
	{
		$this->restrictor->notEquals('color', ['yellow', 'brown']);
		$this->assertEquals(['yellow', 'brown'], $this->restrictor->getUnequal('color'));

		$this->restrictor->notEquals('level', 202);
		$this->assertEquals([202], $this->restrictor->getUnequal('level'));

		$this->restrictor->notEquals('score', [12, 13]);
		$this->assertEquals([12, 13], $this->restrictor->getUnequal('score'));
		$this->assertEquals(['yellow', 'brown'], $this->restrictor->getUnequal('color'));

		$this->assertException(function() {
			$this->restrictor->equals('color', ['blue', 'red']);
		}, 'Shelter\Exception');

		$this->restrictor->notEquals('color', ['white'], Restrictor::UNION);
		$this->assertEquals(['yellow', 'brown', 'white'], $this->restrictor->getUnequal('color'));

		$this->restrictor->notEquals('color', 'black', Restrictor::REPLACE);
		$this->assertEquals(['black'], $this->restrictor->getUnequal('color'));
	}


	public function testInRange()
	{
		$this->restrictor->inRange('score', 10, 15);
		$this->assertEquals([10, 15], $this->restrictor->getRange('score'));

		$this->restrictor->inRange('level', 2, 7);
		$this->assertEquals([2, 7], $this->restrictor->getRange('level'));
		$this->assertEquals([10, 15], $this->restrictor->getRange('score'));

		$this->restrictor->inRange('score', 22, 19);
		$this->assertEquals([19, 22], $this->restrictor->getRange('score'));

		$this->restrictor->inRange('score', NULL, 18);
		$this->assertEquals([NULL, 18], $this->restrictor->getRange('score'));
	}
}
