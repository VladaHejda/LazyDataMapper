<?php

namespace LazyDataMapper\Tests\Suggestor;

use LazyDataMapper,
	LazyDataMapper\Suggestor;

class DescendantsTest extends LazyDataMapper\Tests\TestCase
{

	public function testIterator()
	{
		$paramMap = \Mockery::mock('LazyDataMapper\ParamMap');

		$descendants = [
			'car' => ['Car', FALSE, \Mockery::mock('LazyDataMapper\IIdentifier')],
			'fake' => ['Fake', FALSE, \Mockery::mock('LazyDataMapper\IIdentifier')],
			'drivers' => ['Driver', TRUE, \Mockery::mock('LazyDataMapper\IIdentifier')],
		];

		$suggestorCache = \Mockery::mock('LazyDataMapper\SuggestorCache');

		$suggestorCache
			->shouldReceive('getCached')
			->with($descendants['car'][2], 'Car', FALSE)
			->once()
			->andReturn(new Suggestor($paramMap, $suggestorCache, [], FALSE, $descendants['car'][2]))
		->getMock()
			->shouldReceive('getCached')
			->with($descendants['fake'][2], 'Fake', FALSE)
			->once()
			->andReturnNull()
		->getMock()
			->shouldReceive('getCached')
			->with($descendants['drivers'][2], 'Driver', TRUE)
			->once()
			->andReturn(new Suggestor($paramMap, $suggestorCache, [], TRUE, $descendants['drivers'][2]))
		;

		$identifier = \Mockery::mock('LazyDataMapper\IIdentifier');

		$suggestor = new Suggestor($paramMap, $suggestorCache, [], FALSE, $identifier, $descendants);

		$this->assertTrue($suggestor->hasDescendants());

		// Iterator
		$i = 0;
		foreach ($suggestor as $sourceParam => $descendant) {
			$this->assertInstanceOf('LazyDataMapper\Suggestor', $descendant);

			if ($sourceParam === 'car') {
				$this->assertFalse($descendant->hasDescendants());
				$this->assertSame($descendants['car'][2], $descendant->getIdentifier());
				$this->assertFalse($descendant->isContainer());

			} elseif ($sourceParam === 'drivers') {
				$this->assertFalse($descendant->hasDescendants());
				$this->assertSame($descendants['drivers'][2], $descendant->getIdentifier());
				$this->assertTrue($descendant->isContainer());

			} else {
				$this->fail("Unexpected source parameter '$sourceParam'.");
			}
			++$i;
		}
		$this->assertEquals(2, $i);

		return [$suggestor, $descendants];
	}


	/**
	 * @depends testIterator
	 */
	public function testGet($services)
	{
		list($suggestor, $descendants) = $services;

		// getDescendant()
		$this->assertInstanceOf('LazyDataMapper\Suggestor', $suggestor->getDescendant('car'));
		$this->assertInstanceOf('LazyDataMapper\Suggestor', $suggestor->getDescendant('drivers'));

		// __get()
		$this->assertInstanceOf('LazyDataMapper\Suggestor', $suggestor->car);
		$this->assertFalse($suggestor->car->hasDescendants());
		$this->assertSame($descendants['car'][2], $suggestor->car->getIdentifier());
		$this->assertFalse($suggestor->car->isContainer());

		$this->assertInstanceOf('LazyDataMapper\Suggestor', $suggestor->drivers);
		$this->assertFalse($suggestor->drivers->hasDescendants());
		$this->assertSame($descendants['drivers'][2], $suggestor->drivers->getIdentifier());
		$this->assertTrue($suggestor->drivers->isContainer());

		// "fake" has nothing suggested
		$this->assertNull($suggestor->fake);
		// every nonexistent descendant has to return NULL
		$this->assertNull($suggestor->whatever);
	}
}
