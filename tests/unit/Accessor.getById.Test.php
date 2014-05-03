<?php

namespace LazyDataMapper\Tests\Accessor;

use LazyDataMapper;

class GetByIdTest extends LazyDataMapper\Tests\TestCase
{

	private $data = ['brand' => 'Suzuki', 'color' => 'red'];

	private $childData = ['name' => 'John'];

	/** @var \Mockery\Mock */
	private $entity, $childEntity;

	/** @var LazyDataMapper\Accessor */
	private $accessor;


	protected function setUp()
	{
		parent::setUp();

		$identifier = \Mockery::mock('LazyDataMapper\IIdentifier')
			->shouldReceive('getKey')
			->once()
			->andReturn('someKey')
			->getMock();

		$childIdentifier = \Mockery::mock('LazyDataMapper\IIdentifier')
			->shouldReceive('getKey')
			->twice()
			->andReturn('childKey')
			->getMock();

		$suggestor = \Mockery::mock('LazyDataMapper\Suggestor')
			->shouldReceive('getSuggestions')
			->once()
			->withNoArgs()
			->andReturn(array_keys($this->data))
		->getMock();

		$cache = \Mockery::mock('LazyDataMapper\SuggestorCache')
			->shouldReceive('getCached')
			->once()
			->with($identifier, 'Some\Entity', FALSE, NULL)
			->andReturn($suggestor)
		->getMock();

		$childDataHolder = \Mockery::mock('LazyDataMapper\DataHolder')
			->shouldReceive('hasLoadedChildren')
			->once()
			->andReturn(FALSE)
		->getMock()
			->shouldReceive('getData')
			->once()
			->withNoArgs()
			->andReturn($this->childData)
		->getMock()
			->shouldReceive('getRelations')
			->once()
			->andReturnNull()
		->getMock();

		$childDataHolder
			->shouldReceive('getSuggestor->getIdentifier')
			->once()
			->withNoArgs()
			->andReturn($childIdentifier);

		$dataHolder = $this->mockArrayIterator(\Mockery::mock('LazyDataMapper\DataHolder'), [$childDataHolder])
			->shouldReceive('hasLoadedChildren')
			->once()
			->andReturn(TRUE)
		->getMock()
			->shouldReceive('getData')
			->once()
			->withNoArgs()
			->andReturn($this->data)
		->getMock();

		$mapper = \Mockery::mock('LazyDataMapper\IMapper')
			->shouldReceive('exists')
			->once()
			->with(11)
			->andReturn(TRUE)
		->getMock()
			->shouldReceive('getById')
			->once()
			->with(11, $suggestor, $dataHolder)
			->andReturn($dataHolder)
		->getMock();

		$this->entity = \Mockery::mock('LazyDataMapper\IEntity')
			->shouldReceive('getIdentifier')
			->once()
			->withNoArgs()
			->andReturn($identifier)
		->getMock()
			->shouldReceive('getId')
			->once()
			->andReturn(11)
		->getMock();

		$this->childEntity = \Mockery::mock('LazyDataMapper\IEntity');

		$services = \Mockery::mock('LazyDataMapper\IEntityServiceAccessor')
			->shouldReceive('getMapper')
			->twice()
			->with('Some\Entity')
			->andReturn($mapper)
		->getMock()
			->shouldReceive('composeIdentifier')
			->once()
			->with('Some\Entity', FALSE, NULL, NULL)
			->andReturn($identifier)
		->getMock()
			->shouldReceive('createDataHolder')
			->once()
			->with($suggestor)
			->andReturn($dataHolder)
		->getMock()
			->shouldReceive('composeIdentifier')
			->once()
			->with('Child\Entity', FALSE, $identifier, 'child')
			->andReturn($childIdentifier)
		->getMock();

		$this->accessor = new LazyDataMapper\Accessor($cache, $services);

		$services
			->shouldReceive('createEntity')
			->once()
			->with($this->accessor, 'Some\Entity', 11, $this->data, $identifier)
			->andReturn($this->entity)
		->getMock()
			->shouldReceive('createEntity')
			->once()
			->with($this->accessor, 'Child\Entity', 21, $this->childData, $childIdentifier)
			->andReturn($this->childEntity)
		->getMock();
	}


	public function testGetById()
	{
		$entity = $this->accessor->getEntity('Some\Entity', 11);
		$this->assertSame($this->entity, $entity);

		$childEntity = $this->accessor->getEntity('Child\Entity', 21, $entity, 'child');
		$this->assertSame($this->childEntity, $childEntity);
	}
}
