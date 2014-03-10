<?php

namespace LazyDataMapper\Tests\ParamMap;

use LazyDataMapper;

require_once __DIR__ . '/prepared/ParamMap.php';

class ExceptionsTest extends LazyDataMapper\Tests\TestCase
{

	/**
	 * @expectedException LazyDataMapper\Exception
	 */
	public function testOneDimensionalGetType()
	{
		$paramMap = new OneDimensionalParamMap;
		$paramMap->getMap('unknown');
	}


	/**
	 * @expectedException LazyDataMapper\Exception
	 */
	public function testTwoDimensionalGetUnknownType()
	{
		$paramMap = new TwoDimensionalParamMap;
		$paramMap->getMap('unknown');
	}


	public function testUnknown()
	{
		$paramMap = new OneDimensionalParamMap;

		$this->assertException(function() use ($paramMap) {
			$paramMap->getParamType('something');
		}, 'LazyDataMapper\Exception');

		$this->assertException(function() use ($paramMap) {
			$paramMap->hasType('something');
		}, 'LazyDataMapper\Exception');


		$paramMap = new TwoDimensionalParamMap;

		$this->assertException(function() use ($paramMap) {
			$paramMap->getParamType('unknown');
		}, 'LazyDataMapper\Exception');


		$paramMap = new DefaultParamsParamMap;

		$this->assertException(function() use ($paramMap) {
			$paramMap->getDefaultValue('unknown');
		}, 'LazyDataMapper\Exception');
	}
}
