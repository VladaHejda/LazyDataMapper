<?php

namespace Shelter\Tests;

interface ArrayIteratorMock extends \ArrayAccess, \Iterator, \Countable
{
}

class SelfTest extends TestCase
{

	public function testArrayIteratorMock()
	{
		$mock = \Mockery::mock('Shelter\Tests\ArrayIteratorMock');
		$items = array(
			'zero' => 3,
			'one' => FALSE,
			'two' => 'good job',
			'three' => new \stdClass(),
			'four' => array(),
		);
		$this->mockArrayIterator($mock, $items);

		$this->assertTrue(isset($mock['zero']));
		$this->assertTrue(isset($mock['one']));
		$this->assertTrue(isset($mock['two']));
		$this->assertTrue(isset($mock['three']));
		$this->assertTrue(isset($mock['four']));
		$this->assertFalse(isset($mock['five']));

		$this->assertEquals(3, $mock['zero']);
		$this->assertEquals(FALSE, $mock['one']);
		$this->assertEquals('good job', $mock['two']);
		$this->assertInstanceOf('stdClass', $mock['three']);
		$this->assertEquals(array(), $mock['four']);

		$this->assertCount(5, $mock);

		// both cycles must pass
		for ($n = 0; $n < 2; ++$n) {
			$i = 0;
			reset($items);
			foreach ($mock as $key => $val) {
				if ($i >= 5) {
					$this->fail("Iterator overflow!");
				}
				$this->assertEquals(key($items), $key);
				$this->assertEquals(current($items), $val);
				next($items);
				++$i;
			}
			$this->assertEquals(5, $i);
		}
	}
}
