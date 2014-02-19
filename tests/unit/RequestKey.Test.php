<?php

namespace LazyDataMapper\Tests\RequestKey;

use LazyDataMapper;

class Test extends LazyDataMapper\Tests\TestCase
{

	/** @var LazyDataMapper\RequestKey */
	private $requestKey;


	protected function setUp()
	{
		parent::setUp();
		$this->requestKey = new LazyDataMapper\RequestKey();
	}


	public function testGetKey()
	{
		$key = $this->requestKey->getKey();
		$this->assertTrue(is_string($key));
		return $key;
	}


	/**
	 * @depends testGetKey
	 */
	public function testGetKeyEquality($key)
	{
		$this->assertEquals($key, $this->requestKey->getKey());
	}


	/**
	 * @depends testGetKey
	 */
	public function testGetKeyFromRequestUri($key)
	{
		$_SERVER['REQUEST_URI'] = 'some/request/uri';
		$newKey = $this->requestKey->getKey();
		$this->assertTrue(is_string($newKey));
		$this->assertNotEquals($key, $newKey);
		return $newKey;
	}


	/**
	 * @depends testGetKeyFromRequestUri
	 */
	public function testGetKeyFromRequestUriEquality($key)
	{
		$_SERVER['REQUEST_URI'] = 'some/request/uri';
		$this->assertEquals($key, $this->requestKey->getKey());
	}
}
