<?php

namespace Shelter\Tests;

class ServiceAccessor extends \Shelter\EntityServiceAccessor
{

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
		if (is_string($this->paramMaps[$entityClass])) {
			$serviceClass = $this->paramMaps[$entityClass];
			$this->paramMaps[$entityClass] = new $serviceClass;
		}
		return $this->paramMaps[$entityClass];
	}


	public function getMapper($entityClass)
	{
		if (is_string($this->mappers[$entityClass])) {
			$serviceClass = $this->mappers[$entityClass];
			$this->mappers[$entityClass] = new $serviceClass;
		}
		return $this->mappers[$entityClass];
	}
}
