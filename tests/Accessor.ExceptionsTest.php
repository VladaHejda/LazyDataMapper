<?php

namespace Shelter\Tests\Accessor;

use Shelter;

class ExceptionsTest extends Shelter\Tests\TestCase
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
		$this->mapper = \Mockery::mock('Shelter\IMapper');
		$this->cache = \Mockery::mock('Shelter\ISuggestorCache');

		$this->accessor = \Mockery::mock('Shelter\Accessor[getMapper, getParamMap, composeIdentifier]', array($this->cache))
			->shouldAllowMockingProtectedMethods()
			->shouldReceive('getMapper')
			->with('World\Animal')
			->andReturn($this->mapper)
			->getMock()
			->shouldReceive('getParamMap')
			->with('World\Animal')
			->andReturn($this->paramMap)
			->getMock();
	}


	/**
	 * @expectedException Shelter\Exception
	 */
	public function testGetByIdMapperInvalidReturn()
	{
		$entityClass = 'World\Animal';

		$suggestor = \Mockery::mock('Shelter\ISuggestor');

		$this->mapper
			->shouldReceive('exists')
			->once()
			->with(1)
			->andReturn(TRUE)
			->getMock()
			->shouldReceive('getById')
			->once()
			->with(1, $suggestor)
			->andReturnNull();

		$this->cache
			->shouldReceive('getCached')
			->once()
			->with($entityClass . '#0', $this->paramMap)
			->andReturn($suggestor);

		$this->accessor
			->shouldReceive('composeIdentifier')
			->once()
			->with($entityClass, FALSE, NULL, NULL)
			->andReturn($entityClass . '#0');

		$this->accessor->getById($entityClass, 1);
	}


	/**
	 * @expectedException Shelter\Exception
	 */
	public function testGetByRestrictionsMapperInvalidReturn()
	{
		$entityClass = 'World\Animal';

		$suggestor = \Mockery::mock('Shelter\ISuggestor');

		$restrictor = \Mockery::mock('Shelter\IRestrictor');

		$this->mapper
			->shouldReceive('getIdsByRestrictions')
			->once()
			->with($restrictor)
			->andReturn(array(1))
			->getMock()
			->shouldReceive('getByIdsRange')
			->once()
			->with(array(1), $suggestor)
			->andReturnNull();

		$this->cache
			->shouldReceive('getCached')
			->once()
			->with($entityClass . '~#0', $this->paramMap)
			->andReturn($suggestor);

		$this->accessor
			->shouldReceive('composeIdentifier')
			->once()
			->with($entityClass, TRUE, NULL)
			->andReturn($entityClass . '~#0');

		$this->accessor->getByRestrictions($entityClass, $restrictor);
	}
}
