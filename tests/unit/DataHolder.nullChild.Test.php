<?php

namespace LazyDataMapper\Tests\DataHolder;

use LazyDataMapper,
	LazyDataMapper\DataHolder;

class NullChildTest extends LazyDataMapper\Tests\TestCase
{

	public function testChildIsNull()
	{
		$childSuggestor = \Mockery::mock('LazyDataMapper\Suggestor')
			->shouldReceive('isCollection')
			->andReturn(TRUE)
		->getMock()
			->shouldReceive('getSuggestions')
			->once()
			->andReturn(['book'])
		->getMock()
		;

		$suggestor = \Mockery::mock('LazyDataMapper\Suggestor')
			->shouldReceive('isCollection')
			->andReturn(TRUE)
		->getMock()
			->shouldReceive('getSuggestions')
			->once()
			->andReturn(['name'])
		->getMock()
			->shouldReceive('getChild')
			->once()
			->with('books')
			->andReturn($childSuggestor)
		->getMock()
		;

		$dataHolder = new DataHolder($suggestor);

		$data = [
			['author_id' => 1, 'name' => 'Herman Melville', 'book_id' => 11, 'book' => 'Typee'],
			['author_id' => 1, 'name' => 'Herman Melville', 'book_id' => 12, 'book' => 'Moby-Dick'],
			['author_id' => 2, 'name' => 'Frank Herbert', 'book_id' => 21, 'book' => 'Dune'],
			['author_id' => 2, 'name' => 'Frank Herbert', 'book_id' => 22, 'book' => 'Children of Dune'],
			['author_id' => 3, 'name' => 'John Doe', 'book_id' => NULL, 'book' => NULL],
		];

		$dataHolder->setIdSource('author_id')->setData($data)
			->books->setIdSource('book_id')->setData($data);


		$expectation = [
			1 => ['name' => 'Herman Melville'],
			2 => ['name' => 'Frank Herbert'],
			3 => ['name' => 'John Doe'],
		];
		$this->assertEquals($expectation, $dataHolder->getData());


		$expectation = [
			11 => ['book' => 'Typee'],
			12 => ['book' => 'Moby-Dick'],
			21 => ['book' => 'Dune'],
			22 => ['book' => 'Children of Dune'],
		];
		$this->assertEquals($expectation, $dataHolder->books->getData());


		$expectation = [
			1 => [11, 12],
			2 => [21, 22],
		];
		$this->assertEquals($expectation, $dataHolder->books->getRelations());
	}
}
