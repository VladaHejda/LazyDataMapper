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

	/** @var RequestKey */
	protected $requestKey;

	/** @var IEntityServiceAccessor */
	protected $serviceAccessor;

	/** @var string */
	private $key;


	/**
	 * @param IExternalCache $cache
	 * @param IRequestKey $requestKey
	 * @param IEntityServiceAccessor $serviceAccessor
	 */
	public function __construct(IExternalCache $cache, IRequestKey $requestKey, IEntityServiceAccessor $serviceAccessor)
	{
		$this->externalCache = $cache;
		$this->requestKey = $requestKey;
		$this->serviceAccessor = $serviceAccessor;
	}


	/**
	 * Adds parameter name under one identifier.
	 * @param IIdentifier $identifier
	 * @param string $paramName
	 * @param string $entityClass
	 * @return Suggestor with one suggestion of cached parameter name
	 */
	public function cacheSuggestion(IIdentifier $identifier, $paramName, $entityClass)
	{
		$key = $this->getBaseKey() . $identifier->getKey();
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
	 * @param string $origin
	 * @return void
	 */
	public function cacheChild(IIdentifier $identifier, $childEntityClass, $sourceParam, $origin = IIdentifier::BY_ID)
	{
		$key = $this->getBaseKey() . $identifier->getKey();
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

		$cachedShortcut[$sourceParam] = array($childEntityClass, $origin);
		$this->externalCache->save($key, $cached);
	}


	/**
	 * Gets all cached suggestions under one identifier or NULL when nothing cached.
	 * @param IIdentifier $identifier
	 * @param string $entityClass
	 * @param bool $isCollection
	 * @param array $childrenIdentifierList
	 * @return Suggestor
	 */
	public function getCached(IIdentifier $identifier, $entityClass, $isCollection = FALSE, &$childrenIdentifierList = NULL)
	{
		$childrenIdentifierList = array();

		$cached = $this->externalCache->load($this->getBaseKey() . $identifier->getKey());
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
			$child[] = $childIdentifier = $this->serviceAccessor->composeIdentifier($child[0], $child[1], $identifier, $sourceParam);
			$childrenIdentifierList[] = $childIdentifier->getKey();
		}
		$map = $this->serviceAccessor->getParamMap($entityClass);
		return $this->createSuggestor($map, $identifier, $suggestions, $children, $isCollection);
	}


	/**
	 * @param $suggestions, ...
	 */
	public function forceSuggestions($suggestions)
	{
		$args = func_get_args();
		if (count($args) === 1) {
			$args = $args[0];
		}
		$this->requestKey->addAdditionalInput(is_string($args) ? $args : serialize($args));
		$this->key = NULL;
	}


	/**
	 * @param ParamMap $paramMap
	 * @param IIdentifier $identifier
	 * @param array $suggestions
	 * @param array $children
	 * @param bool $isCollection
	 * @return Suggestor
	 */
	protected function createSuggestor(ParamMap $paramMap, IIdentifier $identifier, array $suggestions, array $children = array(), $isCollection = FALSE)
	{
		return new Suggestor($paramMap, $this, $suggestions, $isCollection, $identifier, $children);
	}


	/**
	 * @return string
	 */
	protected function getBaseKey()
	{
		if ($this->key === NULL) {
			$this->key = $this->requestKey->getKey() . ':';
		}
		return $this->key;
	}


	private function checkCache($cache)
	{
		if (!is_array($cache)) {
			throw new Exception('Malformed cache. Clear it and try again.');
		}
	}
}
