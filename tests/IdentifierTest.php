<?php

namespace Shelter\Tests\Identifier;

/**
 * Test dependent on classes: Shelter\Identifier.
 */

use Shelter;

class Test extends \Shelter\Tests\TestCase
{

	public function testTopEntity()
	{
		$identifier = new Shelter\Identifier('World\Person');
		$this->assertEquals('World\Person#0', $identifier->composeIdentifier());

		$identifier = new Shelter\Identifier('World\Person');
		$this->assertEquals('World\Person#1', $identifier->composeIdentifier());

		$identifier = new Shelter\Identifier('World\Person', TRUE);
		$this->assertEquals('World\Person~#0', $identifier->composeIdentifier());

		$identifier = new Shelter\Identifier('World\Person', TRUE);
		$this->assertEquals('World\Person~#1', $identifier->composeIdentifier());

		$identifier = new Shelter\Identifier('World\Animal', TRUE);
		$this->assertEquals('World\Animal~#0', $identifier->composeIdentifier());

		$identifier = new Shelter\Identifier('World\Animal');
		$this->assertEquals('World\Animal#0', $identifier->composeIdentifier());

		$identifier = new Shelter\Identifier('World\Person');
		$this->assertEquals('World\Person#2', $identifier->composeIdentifier());
	}


	/**
	 * @todo would be single descendant created without sourceParam and otherwise container descendant created with it?
	 *       second case could occur by making some identifier from Restrictor, first one could not automatically occur (even in current plans!)
	 *       but then when creating Identifier with parent must be set sourceParam argument too!
	 *       (when decide to use mentioned behavior - delete two cases of wrong Identifier instancing - or move them to Exception catching tests)
	 *       !! AND modify SuggestorCacheTest - ExternalCache must return sourceParam each time!
	 */
	public function testDescendantEntity()
	{
		$identifier = new Shelter\Identifier('World\Person', FALSE, 'World\Person#0');
		$this->assertEquals('World\Person>World\Person#0', $identifier->composeIdentifier());

		$identifier = new Shelter\Identifier('World\Person', FALSE, 'World\Person#0', 'child_id');
		$this->assertEquals('World\Person|child_id>World\Person#0', $identifier->composeIdentifier());

		$identifier = new Shelter\Identifier('World\Animal', TRUE, 'World\Person#0');
		$this->assertEquals('World\Animal~>World\Person#0', $identifier->composeIdentifier());

		$identifier = new Shelter\Identifier('World\Animal', TRUE, 'World\Person#0', 'pets');
		$this->assertEquals('World\Animal~|pets>World\Person#0', $identifier->composeIdentifier());
	}
}
