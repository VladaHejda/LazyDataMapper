<?php

namespace LazyDataMapper\Tests\ParamMap;

class OneDimensionalParamMap extends \LazyDataMapper\ParamMap
{
	protected function loadMap()
	{
		return ['name', 'age'];
	}
}

class TwoDimensionalParamMap extends \LazyDataMapper\ParamMap
{
	protected function loadMap()
	{
		return [
			'personal' => ['name', 'age'],
			'skill' => ['strength', 'intelligence'],
		];
	}
}

class DefaultParamsParamMap extends \LazyDataMapper\ParamMap
{
	protected function loadMap()
	{
		return ['name', 'age', 'time'];
	}

	protected function loadDefaults()
	{
		return ['age' => 0, 'time' => '01-01-2009'];
	}
}
