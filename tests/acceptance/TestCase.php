<?php

namespace Shelter\Tests;

abstract class AcceptanceTestCase extends TestCase
{

	protected function setUp()
	{
		parent::setUp();
		ResettableIdentifier::resetCounter();
		ServiceAccessor::resetCounters();
	}
}
