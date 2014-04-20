<?php

namespace LazyDataMapper\Tests\Accessor;

use LazyDataMapper;

class GetByRestrictionsTest extends LazyDataMapper\Tests\TestCase
{

	private $data = [
		13 => ['brand' => 'Suzuki', 'color' => 'red'],
		23 => ['brand' => 'Ford', 'color' => 'blue'],
		33 => ['brand' => 'Subaru', 'color' => 'grey'],
	];

	private $childData = [
		55 => ['name' => 'George'],
		65 => ['name' => 'Jack'],
		75 => ['name' => 'Marvin'],
	];

	private $relations = [13 => [55], 23 => [65], 33 => [75]];

	/** @var \Mockery\Mock */
	private $collection, $entity, $childEntity;

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
			->andReturn(array_keys(reset($this->data)))
		->getMock();

		$cache = \Mockery::mock('LazyDataMapper\SuggestorCache')
			->shouldReceive('getCached')
			->once()
			->with($identifier, 'Some\Entity', TRUE)
			->andReturn($suggestor)
		->getMock();

		$childDataHolder = \Mockery::mock('LazyDataMapper\DataHolder')
			->shouldReceive('hasLoadedChildren')
			->once()
			->andReturn(FALSE)
		->getMock()
			->shouldReceive('getParams')
			->once()
			->withNoArgs()
			->andReturn($this->childData)
		->getMock()
			->shouldReceive('getRelations')
			->once()
			->andReturn($this->relations)
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
			->shouldReceive('getParams')
			->once()
			->withNoArgs()
			->andReturn($this->data)
		->getMock();

		$mapper = \Mockery::mock('LazyDataMapper\IMapper')
			->shouldReceive('getByIdsRange')
			->once()
			->with([13, 23, 33], $suggestor, $dataHolder)
			->andReturn($dataHolder)
		->getMock();

		$this->collection = \Mockery::mock('LazyDataMapper\IEntityCollection');

		$this->entity = \Mockery::mock('LazyDataMapper\IEntity')
			->shouldReceive('getIdentifier')
			->once()
			->andReturn($identifier)
		->getMock()
			->shouldReceive('getId')
			->once()
			->andReturn(13)
		->getMock();

		$this->childEntity = \Mockery::mock('LazyDataMapper\IEntity');

		$services = \Mockery::mock('LazyDataMapper\IEntityServiceAccessor')
			->shouldReceive('getMapper')
			->once()
			->with('Some\Entity')
			->andReturn($mapper)
		->getMock()
			->shouldReceive('composeIdentifier')
			->once()
			->with('Some\Entity', TRUE, NULL, NULL)
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
			->shouldReceive('createEntityCollection')
			->once()
			->with($this->accessor, 'Some\Entities', $this->data, $identifier, 'Some\Entity')
			->andReturn($this->collection)
		->getMock()
			->shouldReceive('createEntity')
			->once()
			->with($this->accessor, 'Child\Entity', 55, $this->childData[55], $childIdentifier)
			->andReturn($this->childEntity)
		->getMock();
	}


	public function testGetById()
	{
		$collection = $this->accessor->getByRestrictions(['Some\Entity', 'Some\Entities'], [13, 23, 33]);
		$this->assertSame($this->collection, $collection);

		$childEntity = $this->accessor->getById(['Child\Entity'], 55, $this->entity, 'child');
		$this->assertSame($this->childEntity, $childEntity);
	}
}
