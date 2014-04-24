<?php

namespace LazyDataMapper\Tests\DataHolder;

use LazyDataMapper,
	LazyDataMapper\DataHolder;

class ForksTest extends LazyDataMapper\Tests\TestCase
{

	private $data = [
		[
			'car_id' => 1, 'brand' => 'BMW', 'color' => 'red',
			'driver_id' => 10, 'name' => 'John', 'wins' => 5,
			'team_id' => 100, 'title' => 'Bulls'
		], [
			'car_id' => 1, 'brand' => 'BMW', 'color' => 'red',
			'driver_id' => 20, 'name' => 'Pedro', 'wins' => 3,
			'team_id' => 200, 'title' => 'G&E'
		], [
			'car_id' => 1, 'brand' => 'BMW', 'color' => 'red',
			'driver_id' => 20, 'name' => 'Pedro', 'wins' => 3,
			'team_id' => 300, 'title' => 'Sparks'
		], [
			'car_id' => 2, 'brand' => 'Ford', 'color' => 'blue',
			'driver_id' => 30, 'name' => 'Ed', 'wins' => 7,
			'team_id' => 400, 'title' => 'killerz'
		], [
			'car_id' => 3, 'brand' => 'Saab', 'color' => 'gold',
			'driver_id' => 40, 'name' => 'Hugo', 'wins' => 0,
			'team_id' => 500, 'title' => 'GOGO!'
		], [
			'car_id' => 3, 'brand' => 'Saab', 'color' => 'gold',
			'driver_id' => 40, 'name' => 'Hugo', 'wins' => 0,
			'team_id' => 600, 'title' => 'Mordor'
		],
	];


	private $relations = [
		'drivers' => [1 => [10, 20], 2 => [30], 3 => [40],],
		'teams' => [10 => [100], 20 => [200, 300], 30 => [400], 40 => [500, 600],]
	];


	/** @var DataHolder */
	private $dataHolder;


	// todo test with grouped param map
	protected function setUp()
	{
		parent::setUp();

		$teamSuggestor = \Mockery::mock('LazyDataMapper\Suggestor')
			->shouldReceive('isCollection')
			->andReturn(TRUE)
		->getMock()
			->shouldReceive('getSuggestions')
			->andReturn(['title'])
		->getMock();

		$driverSuggestor = \Mockery::mock('LazyDataMapper\Suggestor')
			->shouldReceive('isCollection')
			->andReturn(TRUE)
		->getMock()
			->shouldReceive('getSuggestions')
			->andReturn(['name', 'wins'])
		->getMock()
			->shouldReceive('getChild')
			->with('teams')
			->andReturn($teamSuggestor)
		->getMock();

		$carSuggestor = \Mockery::mock('LazyDataMapper\Suggestor')
			->shouldReceive('isCollection')
			->andReturn(TRUE)
		->getMock()
			->shouldReceive('getSuggestions')
			->andReturn(['brand', 'color'])
		->getMock()
			->shouldReceive('getChild')
			->with('drivers')
			->andReturn($driverSuggestor)
		->getMock();

		$this->dataHolder = new DataHolder($carSuggestor);
	}


	public function testSetAggregated()
	{
		$this->dataHolder->setIdSource('car_id')->setParams($this->data)
			->drivers->setIdSource('driver_id')->setParams($this->data)
			->teams->setIdSource('team_id')->setParams($this->data);

		$this->doTests();
	}


	public function testSetSorted()
	{
		list($cars, $drivers, $driversRelations, $teams, $teamsRelations) = $this->sortData();

		$this->dataHolder->setParams($cars)
			->drivers->setParentIds($driversRelations)->setParams($drivers)
			->teams->setParentIds($teamsRelations)->setParams($teams);

		$this->doTests();
	}


	public function testSetAggregatedWithParentId()
	{
		$this->dataHolder->drivers->teams->setIdSource('team_id')->setParentIdSource('driver_id')->setParams($this->data)
			->getParent()->setIdSource('driver_id')->setParentIdSource('car_id')->setParams($this->data)
			->getParent()->setIdSource('car_id')->setParams($this->data);

		$this->doTests();
	}


	/**
	 * @expectedException LazyDataMapper\Exception
	 */
	public function testNotSetParentIds()
	{
		$this->dataHolder->drivers->setIdSource('driver_id')->setParams($this->data);
	}


	/**
	 * @expectedException LazyDataMapper\Exception
	 */
	public function testEmptyIdSource()
	{
		unset($this->data[0]['car_id']);
		$this->dataHolder->setIdSource('car_id')->setParams($this->data);
	}


	/**
	 * @expectedException LazyDataMapper\Exception
	 */
	public function testEmptyParentIdSource()
	{
		unset($this->data[0]['car_id']);
		$this->dataHolder->drivers->setIdSource('driver_id')->setParentIdSource('car_id')->setParams($this->data);
	}


	/**
	 * @expectedException LazyDataMapper\Exception
	 */
	public function testWrongId()
	{
		$this->data[1]['car_id'] = 'wrong';
		$this->dataHolder->setIdSource('car_id')->setParams($this->data);
	}


	/**
	 * @expectedException LazyDataMapper\Exception
	 */
	public function testWrongIdFromIndex()
	{
		list($cars) = $this->sortData();
		$cars['wrong'] = $cars[1];
		unset($cars[1]);
		$this->dataHolder->setParams($cars);
	}


	/**
	 * @expectedException LazyDataMapper\Exception
	 */
	public function testWrongParentId()
	{
		$this->data[1]['car_id'] = 'wrong';
		$this->dataHolder->drivers->setIdSource('driver_id')->setParentIdSource('car_id')->setParams($this->data);
	}


	/**
	 * @expectedException LazyDataMapper\Exception
	 */
	public function testMalformedData()
	{
		$this->dataHolder->setIdSource('car_id')->setParams($this->data[1]);
	}


	private function sortData()
	{
		$cars = $drivers = $driversRelations = $teams = $teamsRelations = [];
		foreach ($this->data as $data) {
			$cars[$data['car_id']] = ['brand' => $data['brand'], 'color' => $data['color']];

			$drivers[$data['driver_id']] = ['name' => $data['name'], 'wins' => $data['wins']];
			$driversRelations[$data['driver_id']] = $data['car_id'];

			$teams[$data['team_id']] = ['title' => $data['title']];
			$teamsRelations[$data['team_id']] = $data['driver_id'];
		}

		return [$cars, $drivers, $driversRelations, $teams, $teamsRelations];
	}


	private function doTests()
	{
		// test id relations
		$this->assertNull($this->dataHolder->getRelations());
		$this->assertEquals($this->relations['drivers'], $this->dataHolder->drivers->getRelations());
		$this->assertEquals($this->relations['teams'], $this->dataHolder->drivers->teams->getRelations());

		// test data
		$this->assertEquals(
			[
				1 => ['brand' => 'BMW', 'color' => 'red'],
				2 => ['brand' => 'Ford', 'color' => 'blue'],
				3 => ['brand' => 'Saab', 'color' => 'gold'],
			],
			$this->dataHolder->getParams()
		);
		$this->assertEquals(
			[
				10 => ['name' => 'John', 'wins' => 5],
				20 => ['name' => 'Pedro', 'wins' => 3],
				30 => ['name' => 'Ed', 'wins' => 7],
				40 => ['name' => 'Hugo', 'wins' => 0],
			],
			$this->dataHolder->drivers->getParams()
		);
		$this->assertEquals(
			[
				100 => ['title' => 'Bulls'],
				200 => ['title' => 'G&E'],
				300 => ['title' => 'Sparks'],
				400 => ['title' => 'killerz'],
				500 => ['title' => 'GOGO!'],
				600 => ['title' => 'Mordor'],
			],
			$this->dataHolder->drivers->teams->getParams()
		);
	}
}
