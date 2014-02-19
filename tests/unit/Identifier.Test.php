<?php

namespace LazyDataMapper\Tests\Identifier;

use LazyDataMapper\Identifier;

class Test extends \LazyDataMapper\Tests\TestCase
{

	public function testTopEntity()
	{
		$identifier = new Identifier('World\Person');
		$this->assertEquals('World\Person#0', $identifier->getKey());

		$identifier = new Identifier('World\Person');
		$this->assertEquals('World\Person#1', $identifier->getKey());

		$identifier = new Identifier('World\Person', TRUE);
		$this->assertEquals('World\Person*#0', $identifier->getKey());

		$identifier = new Identifier('World\Person', TRUE);
		$this->assertEquals('World\Person*#1', $identifier->getKey());

		$identifier = new Identifier('World\Animal', TRUE);
		$this->assertEquals('World\Animal*#0', $identifier->getKey());

		$identifier = new Identifier('World\Animal');
		$this->assertEquals('World\Animal#0', $identifier->getKey());

		$identifier = new Identifier('World\Person');
		$this->assertEquals('World\Person#2', $identifier->getKey());
	}


	/**
	 * @depends testTopEntity
	 */
	public function testDescendantEntity()
	{
		$parentIdentifier = new Identifier('World\Building');

		$identifier = new Identifier('World\Person', FALSE, $parentIdentifier, 'child_id');
		$this->assertEquals('World\Person|child_id>World\Building#0', $identifier->getKey());

		$identifier = new Identifier('World\Animal', TRUE, $parentIdentifier);
		$this->assertEquals('World\Animal*>World\Building#0', $identifier->getKey());
	}
}
