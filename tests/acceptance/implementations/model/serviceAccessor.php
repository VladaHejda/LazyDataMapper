<?php

namespace LazyDataMapper\Tests;

use LazyDataMapper;
use LazyDataMapper\IIdentifier;

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

	protected static $mappersList = [
		'CarMapper',
		'DriverMapper',
		'RaceMapper',
	];


	public static function resetCounters()
	{
		foreach (static::$mappersList as $mapper) {
			$mapper = 'LazyDataMapper\Tests\\' . $mapper;
			if (class_exists($mapper)) {
				call_user_func("$mapper::resetCounters");
			}
		}
	}


	public function composeIdentifier($entityClass, $origin = IIdentifier::BY_ID, IIdentifier $parentIdentifier = NULL, $sourceParam = NULL)
	{
		return new ResettableIdentifier($entityClass, $origin, $parentIdentifier, $sourceParam);
	}
}
