<?php

namespace LazyDataMapper\Tests;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/suggestorCache.php';

abstract class AcceptanceTestCase extends TestCase
{

	protected function setUp()
	{
		parent::setUp();
		ResettableIdentifier::resetCounter();
		ServiceAccessor::resetCounters();
		SuggestorCache::resetCounters();
	}
}
