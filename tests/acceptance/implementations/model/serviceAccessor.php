<?php

namespace Shelter\Tests;

class ServiceAccessor extends \Shelter\EntityServiceAccessor
{

	private static $self;


	public function __construct()
	{
		self::$self = $this;
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


	public static function resetStatics()
	{
		$staticVars = ['calledGetById', 'calledGetByRestrictions'];

		foreach (self::$self->mappers as $mapper) {
			if (!class_exists($mapper)) {
				continue;
			}
			foreach ($staticVars as $static) {
				$mapper::$$static = 0;
			}
		}
	}
}
