<?php

namespace LazyDataMapper\Tests\Suggestor;

use LazyDataMapper,
	LazyDataMapper\Suggestor;

class ChildrenTest extends LazyDataMapper\Tests\TestCase
{

	public function testIterator()
	{
		$paramMap = \Mockery::mock('LazyDataMapper\ParamMap');

		$children = [
			'car' => ['Car', FALSE, \Mockery::mock('LazyDataMapper\IIdentifier')],
			'fake' => ['Fake', FALSE, \Mockery::mock('LazyDataMapper\IIdentifier')],
			'drivers' => ['Driver', TRUE, \Mockery::mock('LazyDataMapper\IIdentifier')],
		];

		$suggestorCache = \Mockery::mock('LazyDataMapper\SuggestorCache');

		$suggestorCache
			->shouldReceive('getCached')
			->with($children['car'][2], 'Car', FALSE)
			->once()
			->andReturn(new Suggestor($paramMap, $suggestorCache, [], FALSE, $children['car'][2]))
		->getMock()
			->shouldReceive('getCached')
			->with($children['fake'][2], 'Fake', FALSE)
			->once()
			->andReturnNull()
		->getMock()
			->shouldReceive('getCached')
			->with($children['drivers'][2], 'Driver', TRUE)
			->once()
			->andReturn(new Suggestor($paramMap, $suggestorCache, [], TRUE, $children['drivers'][2]))
		;

		$identifier = \Mockery::mock('LazyDataMapper\IIdentifier');

		$suggestor = new Suggestor($paramMap, $suggestorCache, [], FALSE, $identifier, $children);

		$this->assertTrue($suggestor->hasChildren());

		// Iterator
		$i = 0;
		foreach ($suggestor as $sourceParam => $child) {
			$this->assertInstanceOf('LazyDataMapper\Suggestor', $child);

			if ($sourceParam === 'car') {
				$this->assertFalse($child->hasChildren());
				$this->assertSame($children['car'][2], $child->getIdentifier());
				$this->assertFalse($child->isCollection());

			} elseif ($sourceParam === 'drivers') {
				$this->assertFalse($child->hasChildren());
				$this->assertSame($children['drivers'][2], $child->getIdentifier());
				$this->assertTrue($child->isCollection());

			} else {
				$this->fail("Unexpected source parameter '$sourceParam'.");
			}
			++$i;
		}
		$this->assertEquals(2, $i);

		return [$suggestor, $children];
	}


	/**
	 * @depends testIterator
	 */
	public function testGet($services)
	{
		list($suggestor, $children) = $services;

		// getChild()
		$this->assertInstanceOf('LazyDataMapper\Suggestor', $suggestor->getChild('car'));
		$this->assertInstanceOf('LazyDataMapper\Suggestor', $suggestor->getChild('drivers'));

		// __get()
		$this->assertInstanceOf('LazyDataMapper\Suggestor', $suggestor->car);
		$this->assertFalse($suggestor->car->hasChildren());
		$this->assertSame($children['car'][2], $suggestor->car->getIdentifier());
		$this->assertFalse($suggestor->car->isCollection());

		$this->assertInstanceOf('LazyDataMapper\Suggestor', $suggestor->drivers);
		$this->assertFalse($suggestor->drivers->hasChildren());
		$this->assertSame($children['drivers'][2], $suggestor->drivers->getIdentifier());
		$this->assertTrue($suggestor->drivers->isCollection());

		// "fake" has nothing suggested
		$this->assertNull($suggestor->fake);
		// every nonexistent child has to return NULL
		$this->assertNull($suggestor->whatever);
	}
}
