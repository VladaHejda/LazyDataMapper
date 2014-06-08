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
		$_SERVER['REQUEST_URI'] = 'some/request/uri.html';
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
		$_SERVER['REQUEST_URI'] = 'some/request/uri.html';
		$uriKey = $this->requestKey->getKey();
		$this->assertEquals($key, $uriKey);
	}


	/**
	 * @depends testGetKeyFromRequestUri
	 */
	public function testCuttingQueryString($key)
	{
		$_SERVER['REQUEST_URI'] = 'some/request/uri.html?one=two&three_four';
		$newKey = $this->requestKey->getKey();
		$this->assertEquals($newKey, $key);
	}


	/**
	 * @depends testGetKeyFromRequestUri
	 */
	public function testCuttingAnchor($key)
	{
		$_SERVER['REQUEST_URI'] = 'some/request/uri.html#five';
		$newKey = $this->requestKey->getKey();
		$this->assertEquals($newKey, $key);
	}


	/**
	 * @depends testGetKeyFromRequestUri
	 */
	public function testCuttingQueryStringAndAnchor($key)
	{
		$_SERVER['REQUEST_URI'] = 'some/request/uri.html?six=seven#eight';
		$newKey = $this->requestKey->getKey();
		$this->assertEquals($newKey, $key);
	}


	/**
	 * @depends testGetKeyFromRequestUri
	 */
	public function testAnotherUriInequality($key)
	{
		$_SERVER['REQUEST_URI'] = 'some/request/uri.php?one=two&three_four';
		$newKey = $this->requestKey->getKey();
		$this->assertNotEquals($newKey, $key);
	}


	/**
	 * @depends testGetKey
	 */
	public function testForceKey($key)
	{
		$this->requestKey->forceKey('forced-key');
		$this->assertNotEquals($key, $this->requestKey->getKey());
	}


	/**
	 * @depends testGetKey
	 */
	public function testAdditionalInput($key)
	{
		$this->requestKey->addAdditionalInput('additional-input');
		$this->assertNotEquals($key, $this->requestKey->getKey());
	}
}
