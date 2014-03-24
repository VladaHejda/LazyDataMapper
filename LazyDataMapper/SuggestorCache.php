<?php

namespace LazyDataMapper;

/**
 * Suggestor cache. Caches suggestions per deterministic request (see IRequestKey)
 * for later efficient data load.
 */
class SuggestorCache
{

	const PARAM_NAMES = 0,
		DESCENDANTS = 1;

	/** @var IExternalCache */
	protected $externalCache;

	/** @var string */
	protected $key;

	/** @var IEntityServiceAccessor */
	protected $serviceAccessor;


	/**
	 * @param IExternalCache $cache
	 * @param IRequestKey $requestKey
	 * @param IEntityServiceAccessor $serviceAccessor
	 */
	public function __construct(IExternalCache $cache, IRequestKey $requestKey, IEntityServiceAccessor $serviceAccessor)
	{
		$this->externalCache = $cache;
		$this->key = $requestKey->getKey() . ':';
		$this->serviceAccessor = $serviceAccessor;
	}


	/**
	 * Adds parameter name under one identifier.
	 * @param IIdentifier $identifier
	 * @param string $paramName
	 * @param string $entityClass
	 * @return Suggestor with one suggestion of cached parameter name
	 * @todo rename to cacheSuggestion
	 */
	public function cacheParamName(IIdentifier $identifier, $paramName, $entityClass)
	{
		$key = $this->key . $identifier->getKey();
		$cached = $this->externalCache->load($key);
		if (NULL === $cached) {
			$cached = array();
		} else {
			$this->checkCache($cached);
		}

		if (!isset($cached[self::PARAM_NAMES])) {
			$cached[self::PARAM_NAMES] = array();
		} else {
			$this->checkCache($cached[self::PARAM_NAMES]);
		}

		if (!in_array($paramName, $cached[self::PARAM_NAMES])) {
			$cached[self::PARAM_NAMES][] = $paramName;
			$this->externalCache->save($key, $cached);
		}

		$map = $this->serviceAccessor->getParamMap($entityClass);
		return $this->createSuggestor($map, $identifier, array($paramName));
	}


	/**
	 * Adds child under one identifier.
	 * @param IIdentifier $identifier
	 * @param string $childEntityClass
	 * @param string $sourceParam
	 * @param bool[] $hierarchy
	 * @return void
	 */
	public function cacheChild(IIdentifier $identifier, $childEntityClass, $sourceParam, array $hierarchy = NULL)
	{
		$key = $this->key . $identifier->getKey();
		$cached = $this->externalCache->load($key);

		if (NULL === $cached) {
			$cached = array();
		} else {
			$this->checkCache($cached);
		}

		if (!isset($cached[self::DESCENDANTS])) {
			$cached[self::DESCENDANTS] = array();
		} else {
			$this->checkCache($cached[self::DESCENDANTS]);
		}

		$cachedShortcut = & $cached[self::DESCENDANTS];
		if (!array_key_exists($sourceParam, $cachedShortcut)) {
			$cachedShortcut[$sourceParam] = array();
		} else {
			$this->checkCache($cachedShortcut[$sourceParam]);
			return;
		}

		$cachedShortcut[$sourceParam] = array($childEntityClass, $this->packHierarchy($hierarchy));
		$this->externalCache->save($key, $cached);
	}


	/**
	 * Gets all cached suggestions under one identifier or NULL when nothing cached.
	 * @param IIdentifier $identifier
	 * @param string $entityClass
	 * @param bool[] $hierarchy
	 * @param array $childrenIdentifierList
	 * @return Suggestor
	 */
	public function getCached(IIdentifier $identifier, $entityClass, array $hierarchy = NULL, &$childrenIdentifierList = NULL)
	{
		$childrenIdentifierList = array();

		$cached = $this->externalCache->load($this->key . $identifier->getKey());
		if (NULL === $cached) {
			return NULL;
		} else {
			$this->checkCache($cached);
		}

		if (!isset($cached[self::PARAM_NAMES])) {
			$suggestions = array();
		} else {
			$this->checkCache($cached[self::PARAM_NAMES]);
			$suggestions = $cached[self::PARAM_NAMES];
		}
		if (!isset($cached[self::DESCENDANTS])) {
			$children = array();
		} else {
			$this->checkCache($cached[self::DESCENDANTS]);
			$children = $cached[self::DESCENDANTS];
		}

		// todo toto by mohlo být přesunuto do Suggestoru, ale ten by potom potřeboval serviceAccessor.
		foreach ($children as $sourceParam => &$child) {
			// todo check count $this->checkCache($child, 2);
			$this->checkCache($child);
			$child[1] = $this->unpackHierarchy($child[1]);
			$child[] = $childIdentifier = $this->serviceAccessor->composeIdentifier($child[0], end($child[1]), $identifier, $sourceParam);
			$childrenIdentifierList[] = $childIdentifier->getKey();
		}
		$map = $this->serviceAccessor->getParamMap($entityClass);
		return $this->createSuggestor($map, $identifier, $suggestions, $children, $hierarchy);
	}


	/**
	 * @param ParamMap $paramMap
	 * @param IIdentifier $identifier
	 * @param array $suggestions
	 * @param array $children
	 * @param bool[] $hierarchy
	 * @return Suggestor
	 */
	protected function createSuggestor(ParamMap $paramMap, IIdentifier $identifier, array $suggestions, array $children = array(), array $hierarchy = NULL)
	{
		return new Suggestor($paramMap, $this, $suggestions, $hierarchy, $identifier, $children);
	}


	private function packHierarchy($hierarchy)
	{
		if (!is_array($hierarchy)) {
			return '0';
		}

		$packed = '';
		foreach ($hierarchy as $bit) {
			$packed .= $bit ? '1' : '0';
		}
		return $packed;
	}


	private function unpackHierarchy($packed)
	{
		if (empty($packed)) {
			$this->throwError();
		}

		$hierarchy = str_split($packed);
		foreach ($hierarchy as &$bit) {
			$bit = (bool) $bit;
		}
		return $hierarchy;
	}


	private function checkCache($cache)
	{
		if (!is_array($cache)) {
			$this->throwError();
		}
	}


	private function throwError()
	{
		throw new Exception('Malformed cache. Clear it and try again.');
	}
}
