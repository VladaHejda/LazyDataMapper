<?php

namespace LazyDataMapper\Tests;

use LazyDataMapper;

require_once __DIR__ . '/default.php';
require_once __DIR__ . '/serviceAccessor.php';

class Kitchen extends LazyDataMapper\Entity
{

	protected function getIcebox()
	{
		return $this->getDescendant('LazyDataMapper\Tests\Icebox');
	}
}


class KitchenFacade extends LazyDataMapper\Facade
{}


class KitchenParamMap extends LazyDataMapper\ParamMap
{

	protected $map = ['icebox', 'area', ];
}


class KitchenMapper extends defaultMapper
{

	public static $calledGetById = 0;

	/** @var LazyDataMapper\ISuggestor */
	public static $lastSuggestor;

	public static $data;

	public static $staticData = [
		1 => ['icebox' => '2', 'area' => 22, ],
		2 => ['icebox' => '5', 'area' => 54, ],
	];
}
