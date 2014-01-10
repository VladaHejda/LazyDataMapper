<?php

namespace Shelter\Tests\Accessor;

use Shelter;

class ReadTest extends Shelter\Tests\TestCase
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

		// just for building classes implementing interfaces
		$this->entity = \Mockery::mock('Shelter\IEntity');
		$this->entityContainer = \Mockery::mock('Shelter\IEntityContainer');

		$mockMethods = '[getParamMap, getMapper, getEntityContainerClass, createEntity, createEntityContainer, composeIdentifier]';
		$this->accessor = \Mockery::mock("Shelter\Accessor$mockMethods", array($this->cache))
			->shouldAllowMockingProtectedMethods()
			->shouldReceive('getParamMap')
			->with(get_class($this->entity))
			->andReturn($this->paramMap)
			->getMock()
			->shouldReceive('getMapper')
			->with(get_class($this->entity))
			->andReturn($this->mapper)
			->getMock()
			->shouldReceive('getEntityContainerClass')
			->andReturn(get_class($this->entityContainer))
			->getMock();
	}


	public function testGetByIdNonexistent()
	{
		$this->accessor
			->shouldReceive('composeIdentifier')
			->once();

		$this->mapper
			->shouldReceive('exists')
			->once()
			->with(2)
			->andReturn(FALSE);

		$this->assertNull($this->accessor->getById(get_class($this->entity), 2));
	}


	public function testGetByRestrictionsNothing()
	{
		$identifier = get_class($this->entity) . '~#0';

		$this->accessor
			->shouldReceive('composeIdentifier')
			->once()
			->andReturn($identifier)
			->getMock()
			->shouldReceive('createEntityContainer')
			->once()
			->with(get_class($this->entity), array(), $identifier, NULL)
			->andReturn($this->entityContainer);

		$restrictor = \Mockery::mock('Shelter\IRestrictor');

		$this->mapper
			->shouldReceive('getIdsByRestrictions')
			->once()
			->with($restrictor)
			->andReturnNull();

		$entityContainer = $this->accessor->getByRestrictions(get_class($this->entity), $restrictor);
		$this->assertTrue($this->entityContainer === $entityContainer);
	}


	public function testGetByIdNoneCached()
	{
		$identifier = get_class($this->entity) . '#0';

		$this->accessor
			->shouldReceive('composeIdentifier')
			->once()
			->andReturn($identifier)
			->getMock()
			->shouldReceive('createEntity')
			->once()
			->with(get_class($this->entity), 3, array(), $identifier, NULL)
			->andReturn($this->entity);

		$this->mapper
			->shouldReceive('exists')
			->once()
			->with(3)
			->andReturn(TRUE);

		$this->cache
			->shouldReceive('getCached')
			->once()
			->with($identifier, \Mockery::type(get_class($this->paramMap)))
			->andReturnNull();

		$entity = $this->accessor->getById(get_class($this->entity), 3);
		$this->assertTrue($this->entity === $entity);
	}


	public function testGetByRestrictionsNoneCached()
	{
		$identifier = get_class($this->entity) . '~#1';

		$this->accessor
			->shouldReceive('composeIdentifier')
			->once()
			->andReturn($identifier)
			->getMock()
			->shouldReceive('createEntityContainer')
			->once()
			->with(get_class($this->entity), array(), $identifier, NULL)
			->andReturn($this->entityContainer);

		$this->cache
			->shouldReceive('getCached')
			->once()
			->with($identifier, \Mockery::type(get_class($this->paramMap)))
			->andReturnNull();

		$restrictor = \Mockery::mock('Shelter\IRestrictor');

		$this->mapper
			->shouldReceive('getIdsByRestrictions')
			->once()
			->with($restrictor)
			->andReturn(array(1));

		$entityContainer = $this->accessor->getByRestrictions(get_class($this->entity), $restrictor);
		$this->assertTrue($this->entityContainer === $entityContainer);
	}


	public function testGetParam()
	{
		$identifier = get_class($this->entity) . '#0';

		$this->entity
			->shouldReceive('getIdentifier')
			->once()
			->andReturn($identifier)
			->getMock()
			->shouldReceive('getId')
			->once()
			->andReturn(4);

		$suggestor = \Mockery::mock('Shelter\ISuggestor');

		$this->cache
			->shouldReceive('cacheParamName')
			->once()
			->with($identifier, 'name', $this->paramMap)
			->andReturn($suggestor);

		$holder = \Mockery::mock('Shelter\IDataHolder')
			->shouldReceive('getParams')
			->once()
			->andReturn(array('George'))
			->getMock();

		$this->mapper
			->shouldReceive('getById')
			->once()
			->with(4, $suggestor)
			->andReturn($holder);

		$this->assertEquals('George', $this->accessor->getParam($this->entity, 'name'));
	}


	public function testGetById()
	{
		$identifier = get_class($this->entity) . '#1';
		$data = array('name' => 'John', 'age' => 28);

		$this->accessor
			->shouldReceive('composeIdentifier')
			->once()
			->andReturn($identifier)
			->getMock()
			->shouldReceive('createEntity')
			->once()
			->with(get_class($this->entity), 5, $data, $identifier, NULL)
			->andReturn($this->entity);

		$suggestor = \Mockery::mock('Shelter\ISuggestor');

		$holder = \Mockery::mock('Shelter\IDataholder')
			->shouldReceive('getParams')
			->andReturn($data)
			->getMock();
		$this->mockArrayIterator($holder, array());

		$this->mapper
			->shouldReceive('exists')
			->once()
			->with(5)
			->andReturn(TRUE)
			->getMock()
			->shouldReceive('getById')
			->with(5, $suggestor)
			->andReturn($holder);

		$this->cache
			->shouldReceive('getCached')
			->once()
			->with($identifier, \Mockery::type(get_class($this->paramMap)))
			->andReturn($suggestor);

		$entity = $this->accessor->getById(get_class($this->entity), 5);
		$this->assertTrue($this->entity === $entity);
	}


	public function testGetByRestrictions()
	{
		$identifier = get_class($this->entity) . '~#2';
		$ids = array(2, 3, 4);
		$data = array(
			2 => array('name' => 'Marry'),
			3 => array('name' => 'Laurence'),
			4 => array('name' => 'Yoda'),
		);

		$this->accessor
			->shouldReceive('composeIdentifier')
			->once()
			->andReturn($identifier)
			->getMock()
			->shouldReceive('createEntityContainer')
			->once()
			->with(get_class($this->entity), $data, $identifier, NULL)
			->andReturn($this->entityContainer);

		$suggestor = \Mockery::mock('Shelter\ISuggestor');

		$holder = \Mockery::mock('Shelter\IDataHolder')
			->shouldReceive('getParams')
			->once()
			->andReturn($data)
			->getMock();
		$this->mockArrayIterator($holder, array());

		$this->cache
			->shouldReceive('getCached')
			->once()
			->with($identifier, $this->paramMap)
			->andReturn($suggestor);

		$restrictor = \Mockery::mock('Shelter\IRestrictor');

		$this->mapper
			->shouldReceive('getIdsByRestrictions')
			->once()
			->with($restrictor)
			->andReturn($ids)
			->getMock()
			->shouldReceive('getByIdsRange')
			->once()
			->with($ids, $suggestor)
			->andReturn($holder);

		$entityContainer = $this->accessor->getByRestrictions(get_class($this->entity), $restrictor);
		$this->assertTrue($this->entityContainer === $entityContainer);
	}


	public function testGetByIdWithParent()
	{
		$identifier = get_class($this->entity) . '|animal_id>World#2';

		$parent = \Mockery::mock('Shelter\Entity')
			->shouldReceive('getIdentifier')
			->times(3) // todo meocky výše taky
			->andReturn('World#2')
			->getMock();

		$this->accessor
			->shouldReceive('composeIdentifier')
			->once()
			->with(get_class($this->entity), FALSE, 'World#2', 'animal_id')
		// TODO VČECHNY composeIdentifier AŤ KONTROLUJOU VSTUPY!
			->andReturn($identifier)
			->getMock()
			->shouldReceive('createEntity')
			->once()
			->with(get_class($this->entity), 6, array(), $identifier, $parent)
			->andReturn($this->entity);

		$this->mapper
			->shouldReceive('exists')
			->once()
			->with(6)
			->andReturn(TRUE);

		$this->cache
			->shouldReceive('getCached')
			->once()
			->with($identifier, \Mockery::type(get_class($this->paramMap)))
			->andReturnNull()
			->getMock()
			->shouldReceive('cacheDescendant')
			->once()
			->with('World#2', get_class($this->entity), 'animal_id');

		// todo proč musí entita dostávat parenta?
		$entity = $this->accessor->getById(get_class($this->entity), 6, $parent, 'animal_id');
		$this->assertTrue($this->entity === $entity);
	}


	public function testGetByRestrictionsWithParent()
	{
		$this->markTestIncomplete();
	}
}
