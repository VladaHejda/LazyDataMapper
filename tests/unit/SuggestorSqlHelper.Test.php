<?php

namespace LazyDataMapper\Tests\SuggestorSqlHelper;

use LazyDataMapper;
use LazyDataMapper\SuggestorSqlHelper;

class Test extends LazyDataMapper\Tests\TestCase
{

	/** @var LazyDataMapper\Suggestor */
	private $suggestor;


	protected function setUp()
	{
		parent::setUp();

		$weaponSuggestor = \Mockery::mock('LazyDataMapper\Suggestor')
			->shouldReceive('getSuggestions')
			->atMost(1)
			->andReturn(['name', 'damage', 'wear'])
		->getMock()
		;

		$warriorSuggestor = \Mockery::mock('LazyDataMapper\Suggestor')
			->shouldReceive('getSuggestions')
			->atMost(1)
			->andReturn(['health', 'strength'])
		->getMock()
			->shouldReceive('getChild')
			->atMost(1)
			->with('weapon')
			->andReturn($weaponSuggestor)
		->getMock()
		;

		$this->suggestor = \Mockery::mock('LazyDataMapper\Suggestor')
			->shouldReceive('getSuggestions')
			->atMost(1)
			->with(NULL)
			->andReturn(['race', 'land'])
		->getMock()
			->shouldReceive('getChild')
			->atMost(1)
			->with('warrior')
			->andReturn($warriorSuggestor)
		->getMock()
		;
	}


	public function testBase()
	{
		$helper = new SuggestorSqlHelper($this->suggestor);
		$this->assertEquals('race, land', $helper->build());
	}


	public function testPath()
	{
		$helper = new SuggestorSqlHelper($this->suggestor);
		$helper->setPath('warrior');
		$this->assertEquals(['health', 'strength'], $helper->getRaw());
		$this->assertEquals('health, strength', $helper->build());
	}


	public function testStackedPath()
	{
		$helper = new SuggestorSqlHelper($this->suggestor);
		$helper->setPath('warrior.weapon');
		$this->assertEquals(['name', 'damage', 'wear'], $helper->getRaw());
		$this->assertEquals('name, damage, wear', $helper->build());
	}


	public function testTableAlias()
	{
		$helper = new SuggestorSqlHelper($this->suggestor);
		$helper->setTableAlias('w');
		$this->assertEquals(['w.race', 'w.land'], $helper->getRaw());
		$this->assertEquals('w.race, w.land', $helper->build());

		$this->assertEquals('world.race, world.land', $helper->setTableAlias('world')->build());
	}


	public function testConflicts()
	{
		$helper = new SuggestorSqlHelper($this->suggestor);
		$helper->addConflicts(['land' => 'area']);
		$this->assertEquals(['race', 'land AS area'], $helper->getRaw());
		$this->assertEquals('race, land AS area', $helper->build());

		$this->assertEquals('race AS ethnicity, land AS area', $helper->addConflicts(['race' => 'ethnicity'])->build());
	}


	public function testSuggestorGroup()
	{
		$this->suggestor
			->shouldReceive('getSuggestions')
			->once()
			->with('inventory')
			->andReturn(['food', 'gold']);

		$helper = new SuggestorSqlHelper($this->suggestor);
		$this->assertEquals('food, gold', $helper->setGroup('inventory')->build());
	}


	public function testRulesAggregation()
	{
		$helper = new SuggestorSqlHelper($this->suggestor);
		$helper->setPath('warrior.weapon')->setTableAlias('ARM')->addConflicts(['damage' => 'harm', 'name' => 'weapon']);
		$this->assertEquals(['ARM.name AS weapon', 'ARM.damage AS harm', 'ARM.wear'], $helper->getRaw());
		$this->assertEquals('ARM.name AS weapon, ARM.damage AS harm, ARM.wear', $helper->build());
	}


	public function testReservedWords()
	{
		// phpunit unisolated tests workaround (static variables stay changed across tests)
		SuggestorSqlHelper::setWordWrapper('`');
		SuggestorSqlHelper::$wrapEverything = FALSE;

		SuggestorSqlHelper::$reservedWords[] = 'land';
		$helper = new SuggestorSqlHelper($this->suggestor);
		$this->assertEquals(['race', '`land`'], $helper->getRaw());
		$this->assertEquals('race, `land`', $helper->build());

		SuggestorSqlHelper::setWordWrapper('[]');
		$this->assertEquals('race, [land]', $helper->build());
	}


	public function testWrapEverything()
	{
		SuggestorSqlHelper::setWordWrapper('`');

		SuggestorSqlHelper::$wrapEverything = TRUE;
		$helper = new SuggestorSqlHelper($this->suggestor);
		$this->assertEquals('`race`, `land`', $helper->build());
	}


	public function testWrongConflict()
	{
		$helper = new SuggestorSqlHelper($this->suggestor);
		$this->assertException(function() use ($helper) {
			$helper->addConflicts(['unknown' => 'something'])->build();
		}, 'LazyDataMapper\Exception');
	}


	public function testWrongPath()
	{
		$this->suggestor->shouldReceive('getChild')
			->once()
			->with('unknown')
			->andReturnNull();

		$helper = new SuggestorSqlHelper($this->suggestor);
		$this->assertException(function() use ($helper) {
			$helper->setPath('unknown')->build();
		}, 'LazyDataMapper\Exception');
	}
}
