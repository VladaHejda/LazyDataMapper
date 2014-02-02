<?php

namespace Shelter\Tests\ParamMap;

use Shelter;

require_once __DIR__.'/prepared/ParamMap.php';

class ExceptionsTest extends Shelter\Tests\TestCase
{

	/**
	 * @expectedException Shelter\Exception
	 */
	public function testParamMapOneDimensionalGetType()
	{
		$paramMap = new OneDimensionalParamMap;
		$paramMap->getMap('unknown');
	}


	/**
	 * @expectedException Shelter\Exception
	 */
	public function testParamMapTwoDimensionalGetUnknownType()
	{
		$paramMap = new TwoDimensionalParamMap;
		$paramMap->getMap('unknown');
	}
}
