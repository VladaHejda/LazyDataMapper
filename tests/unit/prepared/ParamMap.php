<?php

namespace LazyDataMapper\Tests\ParamMap;

class OneDimensionalParamMap extends \LazyDataMapper\ParamMap
{
	protected $map = ['name', 'age'];
}

class TwoDimensionalParamMap extends \LazyDataMapper\ParamMap
{
	protected $map = [
		'personal' => ['name', 'age'],
		'skill' => ['strength', 'intelligence'],
	];
}

class DefaultParamsParamMap extends \LazyDataMapper\ParamMap
{
	protected $map = ['name', 'age', 'time'];

	protected $default = ['age' => 0, 'time' => '01-01-2009'];
}
