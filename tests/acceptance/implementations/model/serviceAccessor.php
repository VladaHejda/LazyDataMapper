<?php

namespace LazyDataMapper\Tests;

use LazyDataMapper;

class ResettableIdentifier extends LazyDataMapper\Identifier
{

	static protected $counter = array();


	public static function resetCounter()
	{
		static::$counter = array();
	}
}


class ServiceAccessor extends LazyDataMapper\EntityServiceAccessor
{

	protected static $paramMapsList = [
		'LazyDataMapper\Tests\Icebox' => 'LazyDataMapper\Tests\IceboxParamMap',
		'LazyDataMapper\Tests\Kitchen' => 'LazyDataMapper\Tests\KitchenParamMap',
		'LazyDataMapper\Tests\Car' => 'LazyDataMapper\Tests\CarParamMap',
		'LazyDataMapper\Tests\House' => 'LazyDataMapper\Tests\HouseParamMap',
	];

	protected static $mappersList = [
		'LazyDataMapper\Tests\Icebox' => 'LazyDataMapper\Tests\IceboxMapper',
		'LazyDataMapper\Tests\Kitchen' => 'LazyDataMapper\Tests\KitchenMapper',
		'LazyDataMapper\Tests\Car' => 'LazyDataMapper\Tests\CarMapper',
		'LazyDataMapper\Tests\House' => 'LazyDataMapper\Tests\HouseMapper',
	];

	protected static $checkersList = [
		'LazyDataMapper\Tests\Icebox' => 'LazyDataMapper\Tests\IceboxChecker',
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


	public function composeIdentifier($entityClass, $isContainer = FALSE, LazyDataMapper\IIdentifier $parentIdentifier = NULL, $sourceParam = NULL)
	{
		return new ResettableIdentifier($entityClass, $isContainer, $parentIdentifier, $sourceParam);
	}
}
