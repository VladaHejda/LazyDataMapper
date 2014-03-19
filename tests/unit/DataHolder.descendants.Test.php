<?php

namespace LazyDataMapper\Tests\DataHolder;

use LazyDataMapper,
	LazyDataMapper\DataHolder;

class DescendantsTest extends LazyDataMapper\Tests\TestCase
{

	public function testDescendants()
	{
		$suggestor = \Mockery::mock('LazyDataMapper\Suggestor');
		$suggestor
			->shouldReceive('isContainer')
			->andReturn(FALSE)
		->getMock()
			->shouldReceive('getDescendant')
			->with('whatever')
			->once()
			->andReturnNull()
		;

		$descendants = [
			'car' => \Mockery::mock('LazyDataMapper\Suggestor')
					->shouldReceive('isContainer')
					->andReturn(FALSE)
					->getMock(),
			'drivers' => \Mockery::mock('LazyDataMapper\Suggestor')
					->shouldReceive('isContainer')
					->andReturn(TRUE)
					->getMock(),
		];

		$this->mockArrayIterator($suggestor, $descendants);

		$dataHolder = new DataHolder($suggestor);

		// Iterator
		$i = 0;
		foreach ($dataHolder as $sourceParam => $descendant) {
			$this->assertInstanceOf('LazyDataMapper\DataHolder', $descendant);

			if ($sourceParam === 'car') {
				$this->assertSame($descendants['car'], $descendant->getSuggestor());

			} elseif ($sourceParam === 'drivers') {
				$this->assertSame($descendants['drivers'], $descendant->getSuggestor());

			} else {
				$this->fail("Unexpected source parameter '$sourceParam'.");
			}
			++$i;
		}
		$this->assertEquals(2, $i);

		// getDescendant()
		$this->assertInstanceOf('LazyDataMapper\DataHolder', $dataHolder->getDescendant('car'));
		$this->assertInstanceOf('LazyDataMapper\DataHolder', $dataHolder->getDescendant('drivers'));

		// __get()
		$this->assertInstanceOf('LazyDataMapper\DataHolder', $dataHolder->car);
		$this->assertSame($descendants['car'], $dataHolder->car->getSuggestor());

		$this->assertInstanceOf('LazyDataMapper\DataHolder', $dataHolder->drivers);
		$this->assertSame($descendants['drivers'], $dataHolder->drivers->getSuggestor());

		// every nonexistent descendant has to return NULL
		$this->assertNull($dataHolder->whatever);

		return [$dataHolder, $descendants];
	}


	/**
	 * @depends testDescendants
	 */
	public function testSetParams($services)
	{
		list($dataHolder, $descendants) = $services;

		$descendants['car']->shouldReceive('getParamNames')
			->once()
			->andReturn(['color', 'brand']);

		$dataHolder->car->setParams(['color' => 'blue', 'brand' => 'BMW']);

		$descendants['drivers']->shouldReceive('getParamNames')
			->once()
			->andReturn(['name', 'team']);

		$data = [
			3 => ['name' => 'John', 'team' => 'Storms'],
			7 => ['name' => 'George', 'team' => 'Eagle'],
		];
		$this->assertException(function () use ($dataHolder, $data) {
			$dataHolder->drivers->setParams($data);
		}, 'LazyDataMapper\Exception');

		$dataHolder->drivers->setIds([3, 7]);
		$dataHolder->drivers->setParams($data);
	}
}
