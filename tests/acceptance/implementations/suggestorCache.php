<?php

namespace LazyDataMapper\Tests;

use LazyDataMapper\IIdentifier;

class SuggestorCache extends \LazyDataMapper\SuggestorCache
{

	static $calledCacheParamName, $calledCacheChild, $calledGetCached;


	public static function resetCounters()
	{
		self::$calledCacheParamName = 0;
		self::$calledCacheChild = 0;
		self::$calledGetCached = 0;
	}


	public function cacheParamName(IIdentifier $identifier, $paramName, $entityClass)
	{
		++self::$calledCacheParamName;
		return parent::cacheParamName($identifier, $paramName, $entityClass);
	}


	public function cacheChild(IIdentifier $identifier, $childEntityClass, $sourceParam, $isContainer = FALSE)
	{
		++self::$calledCacheChild;
		parent::cacheChild($identifier, $childEntityClass, $sourceParam, $isContainer);
	}


	public function getCached(IIdentifier $identifier, $entityClass, $isContainer = FALSE, &$childrenIdentifierList = NULL)
	{
		++self::$calledGetCached;
		return parent::getCached($identifier, $entityClass, $isContainer, $childrenIdentifierList);
	}
}
