<?php

namespace Shelter\Tests\Accessor;

use Shelter;

class HelpersTest extends \Shelter\Tests\TestCase
{

	/** @var Shelter\Accessor */
	private $accessor;


	protected function setUp()
	{
		parent::setUp();
		$this->accessor = \Mockery::mock('Shelter\Accessor');
	}


	public function testGetEntityContainerClass()
	{
		$m = new \ReflectionMethod($this->accessor, 'getEntityContainerClass');
		$m->setAccessible(TRUE);
		$this->assertEquals('Products', $m->invoke($this->accessor, 'Product'));
		$this->assertEquals('Countries', $m->invoke($this->accessor, 'Country'));
	}


	public function testSortData()
	{
		$m = new \ReflectionMethod('Shelter\Accessor', 'sortData');
		$m->setAccessible(TRUE);
		$actual   = array(4 => 'A', 5 => 'B', 7 => 'C');
		$expected = array(5 => 'B', 7 => 'C', 4 => 'A');
		$this->assertTrue($expected === $m->invoke($this->accessor, array(5, 7, 4), $actual));
	}


	public function testDescendantsManage()
	{
		$holder = \Mockery::mock('Shelter\IDataHolder');

		$suggestor = \Mockery::mock('Shelter\ISuggestor')
			->shouldReceive('getSourceParam')
			->andReturn('animal_id')
			->getMock()
			->shouldReceive('hasDescendants')
			->andReturn(FALSE)
			->getMock();

		$descendantHolder = \Mockery::mock('Shelter\IDataHolder')
			->shouldReceive('getSuggestor')
			->andReturn($suggestor)
			->getMock()
			->shouldReceive('getParams')
			->andReturn(array('name', 'age'))
			->getMock();

		$this->mockArrayIterator($holder, array('World\Animal' => $descendantHolder));

		$m = new \ReflectionMethod('Shelter\Accessor', 'saveDescendants');
		$m->setAccessible(TRUE);
		$m->invoke($this->accessor, 'World\Person#0', $holder);

		$m = new \ReflectionMethod('Shelter\Accessor', 'getLoadedData');
		$m->setAccessible(TRUE);

		$this->assertEquals(array('name', 'age'), $m->invoke($this->accessor, 'World\Person#0', 'World\Animal', 'animal_id'));
		$this->assertFalse($m->invoke($this->accessor, 'World\Person#0', 'World\Animal', 'unknown'));
		$this->assertFalse($m->invoke($this->accessor, 'World\Person#0', 'World\Unknown', NULL));
	}
}
