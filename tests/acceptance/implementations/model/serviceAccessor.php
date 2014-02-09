<?php

namespace Shelter\Tests;

class ServiceAccessor extends \Shelter\EntityServiceAccessor
{

	public function __construct()
	{
		// reset static vars
		$staticVars = ['calledGetById', 'calledGetByRestrictions'];

		foreach ($this->mappers as $mapper) {
			if (!class_exists($mapper)) {
				continue;
			}
			foreach ($staticVars as $static) {
				$mapper::$$static = 0;
			}
		}
	}


	protected $paramMaps = [
		'Shelter\Tests\Icebox' => 'Shelter\Tests\IceboxParamMap',
		'Shelter\Tests\Kitchen' => 'Shelter\Tests\KitchenParamMap',
		'Shelter\Tests\Car' => 'Shelter\Tests\CarParamMap',
		'Shelter\Tests\House' => 'Shelter\Tests\HouseParamMap',
	];

	protected $mappers = [
		'Shelter\Tests\Icebox' => 'Shelter\Tests\IceboxMapper',
		'Shelter\Tests\Kitchen' => 'Shelter\Tests\KitchenMapper',
		'Shelter\Tests\Car' => 'Shelter\Tests\CarMapper',
		'Shelter\Tests\House' => 'Shelter\Tests\HouseMapper',
	];


	public function getParamMap($entityClass)
	{
		$serviceClass = $this->paramMaps[$entityClass];
		return new $serviceClass;
	}


	public function getMapper($entityClass)
	{
		$serviceClass = $this->mappers[$entityClass];
		return new $serviceClass;
	}
}
