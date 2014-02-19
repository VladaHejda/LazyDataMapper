<?php

namespace LazyDataMapper\Tests;

use LazyDataMapper;

require_once __DIR__ . '/default.php';
require_once __DIR__ . '/serviceAccessor.php';

class Car extends LazyDataMapper\Entity
{}

class CarFacade extends LazyDataMapper\Facade
{}


class CarParamMap extends LazyDataMapper\ParamMap
{

	protected $map = [
		'feature' => ['brand', 'color', ],
		'engine' => ['volume', 'fuel', ],
	];
}


class CarMapper extends defaultMapper
{

	public static $calledGetById = 0;

	/** @var LazyDataMapper\ISuggestor */
	public static $lastSuggestor;

	public static $data;

	public static $staticData = [
		1 => ['brand' => 'Seat', 'color' => 'red', 'volume' => 1.8, 'fuel' => 'diesel', ],
		2 => ['brand' => 'BMW', 'color' => 'black', 'volume' => 3, 'fuel' => 'gas', ],
	];
}
