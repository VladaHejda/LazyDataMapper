<?php

namespace LazyDataMapper\Tests\ParamMap;

use LazyDataMapper;

require_once __DIR__ . '/prepared/ParamMap.php';

class ExceptionsTest extends LazyDataMapper\Tests\TestCase
{

	/**
	 * @expectedException LazyDataMapper\Exception
	 */
	public function testOneDimensionalGetGroup()
	{
		$paramMap = new OneDimensionalParamMap;
		$paramMap->getMap('something');
	}


	/**
	 * @expectedException LazyDataMapper\Exception
	 */
	public function testTwoDimensionalGetUnknownGroup()
	{
		$paramMap = new TwoDimensionalParamMap;
		$paramMap->getMap('unknown');
	}


	public function testUnknown()
	{
		$paramMap = new OneDimensionalParamMap;

		$this->assertException(function() use ($paramMap) {
			$paramMap->getParamGroup('something');
		}, 'LazyDataMapper\Exception');

		$this->assertException(function() use ($paramMap) {
			$paramMap->hasGroup('something');
		}, 'LazyDataMapper\Exception');


		$paramMap = new TwoDimensionalParamMap;

		$this->assertException(function() use ($paramMap) {
			$paramMap->getParamGroup('unknown');
		}, 'LazyDataMapper\Exception');


		$paramMap = new DefaultParamsParamMap;

		$this->assertException(function() use ($paramMap) {
			$paramMap->getDefaultValue('unknown');
		}, 'LazyDataMapper\Exception');
	}
}
