<?php

use LazyDataMapper\IExternalCache,
	LazyDataMapper\Accessor;

/**
 * Call this function to init LazyDataMapper. You will gain the Accessor.
 * @param IExternalCache $cache
 * @return Accessor
 */
function loadLazyDataMapper(IExternalCache $cache)
{
	$requestKey = new \LazyDataMapper\RequestKey;
	$entityServiceAccessor = new \LazyDataMapper\EntityServiceAccessor;
	$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $entityServiceAccessor);
	return new LazyDataMapper\Accessor($suggestorCache, $entityServiceAccessor);
}
