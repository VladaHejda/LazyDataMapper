<?php

namespace Shelter\Tests;

class ServiceAccessor extends \Shelter\EntityServiceAccessor
{

	protected $paramMaps = [
		'Shelter\Tests\Icebox' => 'Shelter\Tests\IceboxParamMap',
		'Shelter\Tests\Kitchen' => 'Shelter\Tests\KitchenParamMap',
	];

	protected $mappers = [
		'Shelter\Tests\Icebox' => 'Shelter\Tests\IceboxMapper',
		'Shelter\Tests\Kitchen' => 'Shelter\Tests\KitchenMapper',
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
