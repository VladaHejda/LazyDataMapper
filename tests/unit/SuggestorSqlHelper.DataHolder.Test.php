<?php

namespace LazyDataMapper\Tests\SuggestorSqlHelper;

use LazyDataMapper;
use LazyDataMapper\SuggestorSqlHelper;

class DataHolderTest extends LazyDataMapper\Tests\TestCase
{

	public function testCompleteDataHolder()
	{
		$animalSuggestor = \Mockery::mock('LazyDataMapper\Suggestor')
			->shouldReceive('isCollection')
			->once()
			->andReturn(FALSE)
		->getMock()
			->shouldReceive('getSuggestions')
			->once()
			->andReturn(['kind', 'name'])
		->getMock()
		;

		$suggestor = \Mockery::mock('LazyDataMapper\Suggestor')
			->shouldReceive('isCollection')
			->once()
			->andReturn(FALSE)
		->getMock()
			->shouldReceive('getChild')
			->once()
			->with('animal')
			->andReturn($animalSuggestor)
		->getMock()
		;

		$data = [
			'id' => 10, 'name' => 'Alfons', 'age' => 152,
			'animal_id' => 20, 'kind' => 'dog', 'dogName' => 'Baryk',
		];

		$animalHolder = \Mockery::mock('LazyDataMapper\DataHolder')
			->shouldReceive('setData')
			->once()
			->with(\Mockery::on(function($arg) {
				return !array_diff_key(['kind' => 'dog', 'name' => 'Baryk'], $arg);
			}))
			->andReturnSelf()
		->getMock()
			->shouldReceive('getSuggestor')
			->once()
			->andReturn($animalSuggestor)
		->getMock()
		;

		$holder = \Mockery::mock('LazyDataMapper\DataHolder')
			->shouldReceive('getChild')
			->once()
			->with('animal')
			->andReturn($animalHolder)
		->getMock()
			->shouldReceive('getSuggestor')
			->times(3)
			->andReturn($suggestor)
		->getMock()
			->shouldReceive('setData')
			->once()
			->with($data)
			->andReturnSelf()
		->getMock()
		;

		$helper0 = new SuggestorSqlHelper($suggestor);
		$helper1 = new SuggestorSqlHelper($suggestor);
		$helper1->setPath('animal')->addConflicts(['name' => 'dogName']);

		$helper0->completeDataHolder($holder, $data);
		$holder = $helper1->completeDataHolder($holder, $data);

		$this->assertInstanceOf('LazyDataMapper\DataHolder', $holder);
	}
}
