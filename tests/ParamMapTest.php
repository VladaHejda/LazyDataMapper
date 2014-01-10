<?php

namespace Shelter\Tests\ParamMap;

use Shelter;

class Test extends Shelter\Tests\TestCase
{

	/** @var Shelter\ParamMap */
	private $paramMap;

	/** @var \ReflectionMethod */
	private $getMap;

	/** @var \ReflectionProperty */
	private $map;


	public function setUp()
	{
		parent::setUp();
		$this->paramMap = \Mockery::mock('Shelter\ParamMap[]');
		$reflection = new \ReflectionClass($this->paramMap);
		$this->map = $reflection->getProperty('map');
		$this->map->setAccessible(TRUE);
		$this->getMap = $reflection->getMethod('getMap');
	}


	public function testParamMapOneDimensional()
	{
		$this->map->setValue($this->paramMap, array('name', 'age'));
		$map = $this->getMap->invoke($this->paramMap);
		$this->assertCount(2, $map);
		$this->assertTrue(array_key_exists('name', $map));
		$this->assertTrue(array_key_exists('age', $map));
	}


	public function testParamMapTwoDimensional()
	{
		$this->map->setValue($this->paramMap, array(
			'personal' => array('name', 'age'),
			'skill' => array('strength', 'intelligence'),
		));
		$map = $this->getMap->invoke($this->paramMap);
		$this->assertCount(2, $map);
		$this->assertTrue(array_key_exists('personal', $map));
		$this->assertTrue(array_key_exists('skill', $map));
		$this->assertTrue(is_array($map['personal']));
		$this->assertTrue(is_array($map['skill']));
		$this->assertTrue(array_key_exists('name', $map['personal']));
		$this->assertTrue(array_key_exists('age', $map['personal']));
		$this->assertTrue(array_key_exists('strength', $map['skill']));
		$this->assertTrue(array_key_exists('intelligence', $map['skill']));
	}
}
