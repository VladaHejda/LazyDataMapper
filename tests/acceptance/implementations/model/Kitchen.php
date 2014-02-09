<?php

namespace Shelter\Tests;

use Shelter;

require_once __DIR__ . '/default.php';
require_once __DIR__ . '/serviceAccessor.php';

class Kitchen extends Shelter\Entity
{

	protected function getIcebox()
	{
		return $this->getDescendant('Shelter\Tests\Icebox', 'icebox');
	}
}


class KitchenFacade extends Shelter\Facade
{}


class KitchenParamMap extends Shelter\ParamMap
{

	protected $map = ['icebox', 'area', ];
}


class KitchenMapper extends defaultMapper
{

	public static $calledGetById = 0;

	public static $lastSuggestor;

	public static $data = [
		1 => ['icebox' => '2', 'area' => 22, ],
		2 => ['icebox' => '5', 'area' => 54, ],
	];
}
