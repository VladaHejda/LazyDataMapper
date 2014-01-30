<?php

require __DIR__.'/load.php';


$cache = new Cache;
$cache->save('571e83d4404fafc9:Icebox#0', array(\Shelter\ISuggestorCache::PARAM_NAMES => array('color')));
$requestKey = new \Shelter\RequestKey;
$serviceAccessor = new ServiceAccessor;
$suggestorCache = new \Shelter\SuggestorCache($cache, $requestKey, $serviceAccessor);
$accessor = new \Shelter\Accessor($suggestorCache, $serviceAccessor);
$iceboxFacade = new IceboxFacade($accessor, $serviceAccessor);

$icebox = $iceboxFacade->getById(2);

var_dump(
	$icebox->color,
	$icebox->food
);

$icebox = $iceboxFacade->getById(4);

var_dump(
	$icebox->capacity
);

sleep(1);
