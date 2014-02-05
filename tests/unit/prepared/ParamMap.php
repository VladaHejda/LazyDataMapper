<?php

namespace Shelter\Tests\ParamMap;

class OneDimensionalParamMap extends \Shelter\ParamMap
{
	protected $map = ['name', 'age'];
}

class TwoDimensionalParamMap extends \Shelter\ParamMap
{
	protected $map = [
		'personal' => ['name', 'age'],
		'skill' => ['strength', 'intelligence'],
	];
}
