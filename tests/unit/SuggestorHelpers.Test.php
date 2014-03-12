<?php

namespace LazyDataMapper\Tests\SuggestorHelpers;

use LazyDataMapper\SuggestorHelpers;

class Test extends \LazyDataMapper\Tests\TestCase
{

	public function testWrappers()
	{
		$paramNames = ['name', 'age', 'address',];

		$this->assertEquals('`name`, `age`, `address`', SuggestorHelpers::wrapColumns($paramNames));

		$this->assertEquals("'name', 'age', 'address'", SuggestorHelpers::wrapParams($paramNames));

		$this->assertEquals('`name` = ?, `age` = ?, `address` = ?', SuggestorHelpers::wrapColumns($paramNames, '= ?'));
	}
}
