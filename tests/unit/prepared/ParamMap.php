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
