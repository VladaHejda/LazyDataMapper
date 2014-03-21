<?php

namespace LazyDataMapper\Tests;

use LazyDataMapper\IIdentifier;

class SuggestorCache extends \LazyDataMapper\SuggestorCache
{

	static $calledCacheParamName, $calledCacheDescendant, $calledGetCached;


	public static function resetCounters()
	{
		self::$calledCacheParamName = 0;
		self::$calledCacheDescendant = 0;
		self::$calledGetCached = 0;
	}


	public function cacheParamName(IIdentifier $identifier, $paramName, $entityClass)
	{
		++self::$calledCacheParamName;
		return parent::cacheParamName($identifier, $paramName, $entityClass);
	}


	public function cacheDescendant(IIdentifier $identifier, $descendantEntityClass, $sourceParam, $isContainer = FALSE)
	{
		++self::$calledCacheDescendant;
		parent::cacheDescendant($identifier, $descendantEntityClass, $sourceParam, $isContainer);
	}


	public function getCached(IIdentifier $identifier, $entityClass, $isContainer = FALSE, &$descendantsIdentifierList = NULL)
	{
		++self::$calledGetCached;
		return parent::getCached($identifier, $entityClass, $isContainer, $descendantsIdentifierList);
	}
}
