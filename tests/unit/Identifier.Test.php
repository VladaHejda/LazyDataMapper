<?php

namespace LazyDataMapper\Tests\Identifier;

use LazyDataMapper\Identifier;

class Test extends \LazyDataMapper\Tests\TestCase
{

	private $restrictionsSign, $idsRangeSign, $oneByRestrictionsSign;


	protected function setUp()
	{
		$this->restrictionsSign = Identifier::BY_RESTRICTIONS;
		$this->idsRangeSign = Identifier::BY_IDS_RANGE;
		$this->oneByRestrictionsSign = Identifier::ONE_BY_RESTRICTIONS;
	}


	public function testTopEntity()
	{
		$identifier = new Identifier('World\Person');
		$this->assertEquals('World\Person#0', $identifier->getKey());

		$identifier = new Identifier('World\Person');
		$this->assertEquals('World\Person#1', $identifier->getKey());

		$identifier = new Identifier('World\Person', Identifier::BY_RESTRICTIONS);
		$this->assertEquals("World\\Person{$this->restrictionsSign}#0", $identifier->getKey());

		$identifier = new Identifier('World\Person', Identifier::BY_RESTRICTIONS);
		$this->assertEquals("World\\Person{$this->restrictionsSign}#1", $identifier->getKey());

		$identifier = new Identifier('World\Animal', Identifier::BY_RESTRICTIONS);
		$this->assertEquals("World\\Animal{$this->restrictionsSign}#0", $identifier->getKey());

		$identifier = new Identifier('World\Animal');
		$this->assertEquals('World\Animal#0', $identifier->getKey());

		$identifier = new Identifier('World\Person');
		$this->assertEquals('World\Person#2', $identifier->getKey(), Identifier::BY_ID);

		$identifier = new Identifier('World\Animal', Identifier::BY_IDS_RANGE);
		$this->assertEquals("World\\Animal{$this->idsRangeSign}#0", $identifier->getKey());

		$identifier = new Identifier('World\Animal', Identifier::ONE_BY_RESTRICTIONS);
		$this->assertEquals("World\\Animal{$this->oneByRestrictionsSign}#0", $identifier->getKey());

		$identifier = new Identifier('World\Animal', Identifier::BY_IDS_RANGE);
		$this->assertEquals("World\\Animal{$this->idsRangeSign}#1", $identifier->getKey());

		$identifier = new Identifier('World\Animal', Identifier::ONE_BY_RESTRICTIONS);
		$this->assertEquals("World\\Animal{$this->oneByRestrictionsSign}#1", $identifier->getKey());
	}


	/**
	 * @depends testTopEntity
	 */
	public function testChildEntity()
	{
		$parentIdentifier = new Identifier('World\Building');

		$identifier = new Identifier('World\Person', Identifier::BY_ID, $parentIdentifier, 'child_id');
		$this->assertEquals('World\Person|child_id>World\Building#0', $identifier->getKey());

		$identifier = new Identifier('World\Animal', Identifier::BY_RESTRICTIONS, $parentIdentifier);
		$this->assertEquals("World\\Animal{$this->restrictionsSign}>World\\Building#0", $identifier->getKey());

		$identifier = new Identifier('World\Animal', Identifier::BY_IDS_RANGE, $parentIdentifier);
		$this->assertEquals("World\\Animal{$this->idsRangeSign}>World\\Building#0", $identifier->getKey());

		$identifier = new Identifier('World\Animal', Identifier::ONE_BY_RESTRICTIONS, $parentIdentifier);
		$this->assertEquals("World\\Animal{$this->oneByRestrictionsSign}>World\\Building#0", $identifier->getKey());

		$parentIdentifier = new Identifier('World\Building', Identifier::BY_RESTRICTIONS);

		$identifier = new Identifier('World\Animal', Identifier::BY_ID, $parentIdentifier);
		$this->assertEquals("World\\Animal>World\\Building{$this->restrictionsSign}#0", $identifier->getKey());

		$identifier = new Identifier('World\Animal', Identifier::BY_RESTRICTIONS, $parentIdentifier);
		$this->assertEquals("World\\Animal{$this->restrictionsSign}>World\\Building{$this->restrictionsSign}#0", $identifier->getKey());

		$identifier = new Identifier('World\Animal', Identifier::BY_IDS_RANGE, $parentIdentifier);
		$this->assertEquals("World\\Animal{$this->idsRangeSign}>World\\Building{$this->restrictionsSign}#0", $identifier->getKey());

		$identifier = new Identifier('World\Animal', Identifier::ONE_BY_RESTRICTIONS, $parentIdentifier);
		$this->assertEquals("World\\Animal{$this->oneByRestrictionsSign}>World\\Building{$this->restrictionsSign}#0", $identifier->getKey());
	}
}
