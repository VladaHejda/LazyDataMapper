<?php

namespace LazyDataMapper\Tests\ParamMap;

use LazyDataMapper;

require_once __DIR__ . '/prepared/ParamMap.php';

class ExceptionsTest extends LazyDataMapper\Tests\TestCase
{

	/**
	 * @expectedException LazyDataMapper\Exception
	 */
	public function testParamMapOneDimensionalGetType()
	{
		$paramMap = new OneDimensionalParamMap;
		$paramMap->getMap('unknown');
	}


	/**
	 * @expectedException LazyDataMapper\Exception
	 */
	public function testParamMapTwoDimensionalGetUnknownType()
	{
		$paramMap = new TwoDimensionalParamMap;
		$paramMap->getMap('unknown');
	}
}
