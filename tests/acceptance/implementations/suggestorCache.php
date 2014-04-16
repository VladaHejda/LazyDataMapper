<?php

namespace LazyDataMapper\Tests;

use LazyDataMapper\IIdentifier;

class SuggestorCache extends \LazyDataMapper\SuggestorCache
{

	static $calledCacheSuggestion, $calledCacheChild, $calledGetCached;


	public static function resetCounters()
	{
		self::$calledCacheSuggestion = 0;
		self::$calledCacheChild = 0;
		self::$calledGetCached = 0;
	}


	public function cacheSuggestion(IIdentifier $identifier, $paramName, $entityClass)
	{
		++self::$calledCacheSuggestion;
		return parent::cacheSuggestion($identifier, $paramName, $entityClass);
	}


	public function cacheChild(IIdentifier $identifier, $childEntityClass, $sourceParam, $isCollection = FALSE)
	{
		++self::$calledCacheChild;
		parent::cacheChild($identifier, $childEntityClass, $sourceParam, $isCollection);
	}


	public function getCached(IIdentifier $identifier, $entityClass, $isCollection = FALSE, &$childrenIdentifierList = NULL)
	{
		++self::$calledGetCached;
		return parent::getCached($identifier, $entityClass, $isCollection, $childrenIdentifierList);
	}
}
