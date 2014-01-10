<?php

namespace Shelter\Tests\Accessor;

/**
 * Test dependent on classes: Shelter\Accessor, Shelter\DataHolder, Shelter\Identifier.
 */

use Shelter;

class EntityTest extends Shelter\Tests\TestCase
{

	/** @var Shelter\Accessor */
	private $accessor;

	/** @var \Mockery\Mock */
	private $cache;

	/** @var \Mockery\Mock */
	private $entity;

	/** @var \Mockery\Mock */
	private $entityContainer;

	/** @var \Mockery\Mock */
	private $paramMap;

	/** @var \Mockery\Mock */
	private $mapper;


	protected function setUp()
	{
		parent::setUp();

		$this->paramMap = \Mockery::mock('Shelter\IParamMap');
		$this->cache = \Mockery::mock('Shelter\ISuggestorCache');
		$this->mapper = \Mockery::mock('Shelter\IMapper');
		$this->entity = \Mockery::mock('Shelter\IEntity');

		$this->accessor = \Mockery::mock('Shelter\Accessor[getParamMap, getMapper]', array($this->cache))
			->shouldReceive('getParamMap')
			->with(get_class($this->entity))
			->andReturn($this->paramMap)
			->getMock()
			->shouldReceive('getMapper')
			->with(get_class($this->entity))
			->andReturn($this->mapper)
			->getMock();
	}


	public function testCreate()
	{
		$this->paramMap
			->shouldReceive('getMap')
			->andReturn(array('name' => NULL));

		$this->mapper
			->shouldReceive('create')
			->once()
			->with(\Mockery::on(function($holder){
				if (!$holder instanceof Shelter\DataHolder) {
					return FALSE;
				}
				if (array('name' => 'Jacob') != $holder->getParams()) {
					return FALSE;
				}
				return TRUE;
			}))
			->andReturn(5);

       	$this->assertEquals(5, $this->accessor->create(get_class($this->entity), array('name' => 'Jacob')));
	}


	public function testSave()
	{
		$this->entity
			->shouldReceive('getId')
			->andReturn(3)
			->getMock()
			->shouldReceive('getChanges')
			->once()
			->andReturn(array('gender' => 'm'));

		$this->mapper
			->shouldReceive('save')
			->once()
			->with(3, \Mockery::on(function($holder){
				if (!$holder instanceof Shelter\DataHolder) {
					return FALSE;
				}
				if (array('gender' => 'm') != $holder->getParams()) {
					return FALSE;
				}
				return TRUE;
			}));

		$this->accessor->save($this->entity);
	}


	public function testRemove()
	{

	}
}
