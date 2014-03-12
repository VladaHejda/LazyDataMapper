<?php

namespace LazyDataMapper\Tests;

use LazyDataMapper;

require_once __DIR__ . '/default.php';
require_once __DIR__ . '/serviceAccessor.php';

class xCar extends LazyDataMapper\Entity
{}

class xCarFacade extends LazyDataMapper\Facade
{}


class xCarParamMap extends LazyDataMapper\ParamMap
{

	protected $map = [
		'feature' => ['brand', 'color', ],
		'engine' => ['volume', 'fuel', ],
	];
}


class xCarMapper extends defaultMapper
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
