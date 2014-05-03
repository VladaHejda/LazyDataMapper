<?php

namespace LazyDataMapper\Tests\DataHolder;

use LazyDataMapper,
	LazyDataMapper\DataHolder;

class ChildrenTest extends LazyDataMapper\Tests\TestCase
{

	public function testChildren()
	{
		$suggestor = \Mockery::mock('LazyDataMapper\Suggestor');
		$suggestor
			->shouldReceive('isCollection')
			->andReturn(FALSE)
		->getMock()
			->shouldReceive('getChild')
			->with('whatever')
			->once()
			->andReturnNull()
		;

		$children = [
			'car' => \Mockery::mock('LazyDataMapper\Suggestor')
					->shouldReceive('isCollection')
					->andReturn(FALSE)
					->getMock(),
			'drivers' => \Mockery::mock('LazyDataMapper\Suggestor')
					->shouldReceive('isCollection')
					->andReturn(TRUE)
					->getMock(),
		];

		$this->mockArrayIterator($suggestor, $children);

		$dataHolder = new DataHolder($suggestor);

		// Iterator
		$i = 0;
		foreach ($dataHolder as $sourceParam => $child) {
			$this->assertInstanceOf('LazyDataMapper\DataHolder', $child);

			if ($sourceParam === 'car') {
				$this->assertSame($children['car'], $child->getSuggestor());

			} elseif ($sourceParam === 'drivers') {
				$this->assertSame($children['drivers'], $child->getSuggestor());

			} else {
				$this->fail("Unexpected source parameter '$sourceParam'.");
			}
			++$i;
		}
		$this->assertEquals(2, $i);

		// getChild()
		$this->assertInstanceOf('LazyDataMapper\DataHolder', $dataHolder->getChild('car'));
		$this->assertInstanceOf('LazyDataMapper\DataHolder', $dataHolder->getChild('drivers'));

		// __get()
		$this->assertInstanceOf('LazyDataMapper\DataHolder', $dataHolder->car);
		$this->assertSame($children['car'], $dataHolder->car->getSuggestor());

		$this->assertInstanceOf('LazyDataMapper\DataHolder', $dataHolder->drivers);
		$this->assertSame($children['drivers'], $dataHolder->drivers->getSuggestor());

		// every nonexistent child has to return NULL
		$this->assertNull($dataHolder->whatever);

		return [$dataHolder, $children];
	}


	/**
	 * @depends testChildren
	 */
	public function testSetData($services)
	{
		list($dataHolder, $children) = $services;

		$children['car']->shouldReceive('getSuggestions')
			->once()
			->andReturn(['color', 'brand']);

		$dataHolder->car->setData(['color' => 'blue', 'brand' => 'BMW']);

		$children['drivers']->shouldReceive('getSuggestions')
			->once()
			->andReturn(['name', 'team']);

		$data = [
			3 => ['name' => 'John', 'team' => 'Storms'],
			7 => ['name' => 'George', 'team' => 'Eagle'],
		];
		$dataHolder->drivers->setData($data);
	}
}
