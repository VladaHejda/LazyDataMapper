<?php

namespace LazyDataMapper\Tests\ParamMap;

use LazyDataMapper;

require_once __DIR__ . '/prepared/ParamMap.php';

class Test extends LazyDataMapper\Tests\TestCase
{

	public function testOneDimensional()
	{
		$paramMap = new OneDimensionalParamMap;

		$this->assertFalse($paramMap->isSeparatedByType());
		$this->assertTrue($paramMap->hasParam('name'));
		$this->assertFalse($paramMap->hasParam('unknown'));

		$map = $paramMap->getMap();
		$this->assertCount(2, $map);
		$this->assertTrue(array_key_exists('name', $map));
		$this->assertTrue(array_key_exists('age', $map));
	}


	public function testTwoDimensional()
	{
		$paramMap = new TwoDimensionalParamMap;

		$this->assertTrue($paramMap->isSeparatedByType());
		$this->assertTrue($paramMap->hasParam('strength'));
		$this->assertFalse($paramMap->hasParam('unknown'));
		$this->assertTrue($paramMap->hasType('skill'));
		$this->assertFalse($paramMap->hasType('unknown'));
		$this->assertEquals('skill', $paramMap->getParamType('intelligence'));

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


	public function testOneDimensionalNoFlip()
	{
		$paramMap = new OneDimensionalParamMap;
		$map = $paramMap->getMap(NULL, FALSE);
		$this->assertCount(2, $map);
		$this->assertTrue(in_array('name', $map));
		$this->assertTrue(in_array('age', $map));
	}


	public function testTwoDimensionalNoFlip()
	{
		$paramMap = new TwoDimensionalParamMap;
		$map = $paramMap->getMap(NULL, FALSE);
		$this->assertCount(2, $map);
		$this->assertTrue(in_array('name', $map['personal']));
		$this->assertTrue(in_array('age', $map['personal']));
		$this->assertTrue(in_array('strength', $map['skill']));
		$this->assertTrue(in_array('intelligence', $map['skill']));
	}


	public function testTwoDimensionalGetType()
	{
		$paramMap = new TwoDimensionalParamMap;
		$map = $paramMap->getMap('personal');
		$this->assertCount(2, $map);
		$this->assertTrue(array_key_exists('name', $map));
		$this->assertTrue(array_key_exists('age', $map));
	}


	public function testTwoDimensionalGetTypeNoFlip()
	{
		$paramMap = new TwoDimensionalParamMap;
		$map = $paramMap->getMap('skill', FALSE);
		$this->assertCount(2, $map);
		$this->assertTrue(in_array('strength', $map));
		$this->assertTrue(in_array('intelligence', $map));
	}


	public function testDefaultParams()
	{
		$paramMap = new DefaultParamsParamMap;
		$this->assertNull($paramMap->getDefaultValue('name'));
		$this->assertTrue(0 === $paramMap->getDefaultValue('age'));
		$this->assertEquals('01-01-2009', $paramMap->getDefaultValue('time'));
	}
}
