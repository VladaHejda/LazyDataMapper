<?php

namespace LazyDataMapper\Tests\ParamMap;

use LazyDataMapper;

require_once __DIR__ . '/prepared/ParamMap.php';

class Test extends LazyDataMapper\Tests\TestCase
{

	public function testParamMapOneDimensional()
	{
		$paramMap = new OneDimensionalParamMap;
		$map = $paramMap->getMap();
		$this->assertCount(2, $map);
		$this->assertTrue(array_key_exists('name', $map));
		$this->assertTrue(array_key_exists('age', $map));
	}


	public function testParamMapTwoDimensional()
	{
		$paramMap = new TwoDimensionalParamMap;
		$map = $paramMap->getMap();
		$this->assertCount(2, $map);
		$this->assertTrue(isset($map['personal']));
		$this->assertTrue(isset($map['skill']));
		$this->assertTrue(is_array($map['personal']));
		$this->assertTrue(is_array($map['skill']));
		$this->assertTrue(array_key_exists('name', $map['personal']));
		$this->assertTrue(array_key_exists('age', $map['personal']));
		$this->assertTrue(array_key_exists('strength', $map['skill']));
		$this->assertTrue(array_key_exists('intelligence', $map['skill']));
	}


	public function testParamMapOneDimensionalNoFlip()
	{
		$paramMap = new OneDimensionalParamMap;
		$map = $paramMap->getMap(NULL, FALSE);
		$this->assertCount(2, $map);
		$this->assertTrue(in_array('name', $map));
		$this->assertTrue(in_array('age', $map));
	}


	public function testParamMapTwoDimensionalNoFlip()
	{
		$paramMap = new TwoDimensionalParamMap;
		$map = $paramMap->getMap(NULL, FALSE);
		$this->assertCount(2, $map);
		$this->assertTrue(in_array('name', $map['personal']));
		$this->assertTrue(in_array('age', $map['personal']));
		$this->assertTrue(in_array('strength', $map['skill']));
		$this->assertTrue(in_array('intelligence', $map['skill']));
	}


	public function testParamMapTwoDimensionalGetType()
	{
		$paramMap = new TwoDimensionalParamMap;
		$map = $paramMap->getMap('personal');
		$this->assertCount(2, $map);
		$this->assertTrue(array_key_exists('name', $map));
		$this->assertTrue(array_key_exists('age', $map));
	}


	public function testParamMapTwoDimensionalGetTypeNoFlip()
	{
		$paramMap = new TwoDimensionalParamMap;
		$map = $paramMap->getMap('skill', FALSE);
		$this->assertCount(2, $map);
		$this->assertTrue(in_array('strength', $map));
		$this->assertTrue(in_array('intelligence', $map));
	}
}
