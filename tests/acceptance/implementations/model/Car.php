<?php

namespace Shelter\Tests;

use Shelter;

require_once __DIR__ . '/default.php';
require_once __DIR__ . '/serviceAccessor.php';

class Car extends Shelter\Entity
{}

class CarFacade extends Shelter\Facade
{}


class CarParamMap extends Shelter\ParamMap
{

	protected $map = [
		'feature' => ['brand', 'color', ],
		'engine' => ['volume', 'fuel', ],
	];
}


class CarMapper extends defaultMapper
{

	public static $calledGetById = 0;

	public static $lastSuggestor;

	public static $data = [
		1 => ['brand' => 'Seat', 'color' => 'red', 'volume' => 1.8, 'fuel' => 'diesel', ],
		2 => ['brand' => 'BMW', 'color' => 'black', 'volume' => 3, 'fuel' => 'gas', ],
	];
}
