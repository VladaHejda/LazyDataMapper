<?php

namespace Shelter\Tests\ParamMap;

class OneDimensionalParamMap extends \Shelter\ParamMap
{
	protected $map = array('name', 'age');
}

class TwoDimensionalParamMap extends \Shelter\ParamMap
{
	protected $map = array(
		'personal' => array('name', 'age'),
		'skill' => array('strength', 'intelligence'),
	);
}
