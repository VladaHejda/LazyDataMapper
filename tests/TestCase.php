<?php

namespace LazyDataMapper\Tests;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{

	protected function tearDown()
	{
		parent::tearDown();
		\Mockery::close();
	}


	protected function assertException(callable $callback, $expectedException = 'Exception', $expectedCode = NULL, $expectedMessage = NULL)
	{
		if (!class_exists($expectedException) || interface_exists($expectedException)) {
			$this->fail("An exception of type '$expectedException' does not exist.");
		}

		try {
			$callback();
		} catch (\Exception $e) {
			$class = get_class($e);
			$message = $e->getMessage();
			$code = $e->getCode();

			$extraInfo = $message ? " (message was $message, code was $code)" : ($code ? " (code was $code)" : '');
			$this->assertInstanceOf($expectedException, $e, "Failed asserting the class of exception$extraInfo.");

			if (NULL !== $expectedCode) {
				$this->assertEquals($expectedCode, $code, "Failed asserting code of thrown $class.");
			}
			if (NULL !== $expectedMessage) {
				$this->assertContains($expectedMessage, $message, "Failed asserting the message of thrown $class.");
			}
			return;
		}

		$extraInfo = $expectedException !== 'Exception' ? " of type $expectedException" : '';
		$this->fail("Failed asserting that exception$extraInfo was thrown.");
	}


	protected function mockArrayIterator(\Mockery\MockInterface $mock, array $items)
	{
		if ($mock instanceof \ArrayAccess) {
			foreach ($items as $key => $val) {
				$mock->shouldReceive('offsetGet')
					->with($key)
					->andReturn($val);

				$mock->shouldReceive('offsetExists')
					->with($key)
					->andReturn(TRUE);
			}

			$mock->shouldReceive('offsetExists')
				->andReturn(FALSE);
		}

		if ($mock instanceof \Iterator) {
			$counter = 0;

			$mock->shouldReceive('rewind')
				->andReturnUsing(function () use (& $counter) {
					$counter = 0;
				});

			$vals = array_values($items);
			$keys = array_values(array_keys($items));

			$mock->shouldReceive('valid')
				->andReturnUsing(function () use (& $counter, $vals) {
					return isset($vals[$counter]);
				});

			$mock->shouldReceive('current')
				->andReturnUsing(function () use (& $counter, $vals) {
					return $vals[$counter];
				});

			$mock->shouldReceive('key')
				->andReturnUsing(function () use (& $counter, $keys) {
					return $keys[$counter];
				});

			$mock->shouldReceive('next')
				->andReturnUsing(function () use (& $counter) {
					++$counter;
				});
		}

		if ($mock instanceof \Countable) {
			$mock->shouldReceive('count')
				->andReturn(count($items));
		}

		return $mock;
	}
}
