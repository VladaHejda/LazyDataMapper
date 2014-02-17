<?php

namespace Shelter\Tests;

use Shelter;

class ResettableIdentifier extends Shelter\Identifier
{

	static protected $counter = array();


	public static function resetCounter()
	{
		static::$counter = array();
	}
}


class ServiceAccessor extends Shelter\EntityServiceAccessor
{

	protected static $paramMapsList = [
		'Shelter\Tests\Icebox' => 'Shelter\Tests\IceboxParamMap',
		'Shelter\Tests\Kitchen' => 'Shelter\Tests\KitchenParamMap',
		'Shelter\Tests\Car' => 'Shelter\Tests\CarParamMap',
		'Shelter\Tests\House' => 'Shelter\Tests\HouseParamMap',
	];

	protected static $mappersList = [
		'Shelter\Tests\Icebox' => 'Shelter\Tests\IceboxMapper',
		'Shelter\Tests\Kitchen' => 'Shelter\Tests\KitchenMapper',
		'Shelter\Tests\Car' => 'Shelter\Tests\CarMapper',
		'Shelter\Tests\House' => 'Shelter\Tests\HouseMapper',
	];

	protected static $checkersList = [
		'Shelter\Tests\Icebox' => 'Shelter\Tests\IceboxChecker',
	];

	protected $paramMaps = [];

	protected $mappers = [];

	protected $checkers = [];


	public static function resetCounters()
	{
		foreach (static::$mappersList as $mapper) {
			if (class_exists($mapper)) {
				call_user_func("$mapper::resetCounters");
			}
		}
	}


	public function getParamMap($entityClass)
	{
		if (!isset($this->paramMaps[$entityClass])) {
			$serviceClass = static::$paramMapsList[$entityClass];
			$this->paramMaps[$entityClass] = new $serviceClass;
		}
		return $this->paramMaps[$entityClass];
	}


	public function getMapper($entityClass)
	{
		if (!isset($this->mappers[$entityClass])) {
			$serviceClass = static::$mappersList[$entityClass];
			$this->mappers[$entityClass] = new $serviceClass;
		}
		return $this->mappers[$entityClass];
	}


	public function getChecker($entityClass)
	{
		if (!isset(static::$checkersList[$entityClass])) {
			return NULL;
		}

		if (!isset($this->checkers[$entityClass])) {
			$serviceClass = static::$checkersList[$entityClass];
			$this->checkers[$entityClass] = new $serviceClass;
		}
		return $this->checkers[$entityClass];
	}


	public function composeIdentifier($entityClass, $isContainer = FALSE, Shelter\IIdentifier $parentIdentifier = NULL, $sourceParam = NULL)
	{
		return new ResettableIdentifier($entityClass, $isContainer, $parentIdentifier, $sourceParam);
	}
}
