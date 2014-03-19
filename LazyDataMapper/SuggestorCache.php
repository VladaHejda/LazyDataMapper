<?php

namespace LazyDataMapper;

class SuggestorCache implements ISuggestorCache
{

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
	 * @param IIdentifier $identifier
	 * @param string $paramName
	 * @param string $entityClass
	 * @return ISuggestor
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

		$map = $this->serviceAccessor->getParamMap($entityClass);
		$suggestor = $this->createSuggestor($map, $identifier, array($paramName));

		if (!in_array($paramName, $cached[self::PARAM_NAMES])) {
			$cached[self::PARAM_NAMES][] = $paramName;
			$this->externalCache->save($key, $cached);
		}

		return $suggestor;
	}


	/**
	 * @param IIdentifier $identifier
	 * @param string $descendantEntityClass
	 * @param string $sourceParam
	 * @param bool $isContainer
	 * @return void
	 */
	public function cacheDescendant(IIdentifier $identifier, $descendantEntityClass, $sourceParam, $isContainer = FALSE)
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
		if (!isset($cachedShortcut[$descendantEntityClass])) {
			$cachedShortcut[$descendantEntityClass] = array();
		} else {
			$this->checkCache($cachedShortcut[$descendantEntityClass]);
		}

		$cachedShortcut = & $cachedShortcut[$descendantEntityClass];
		if (array_key_exists($sourceParam, $cachedShortcut)) {
			return;
		}
		$cachedShortcut[$sourceParam] = array($isContainer);
		$this->externalCache->save($key, $cached);
	}


	/**
	 * @param IIdentifier $identifier
	 * @param string $entityClass
	 * @param bool $isContainer
	 * @return ISuggestor
	 */
	public function getCached(IIdentifier $identifier, $entityClass, $isContainer = FALSE)
	{
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
			$descendants = array();
		} else {
			$this->checkCache($cached[self::DESCENDANTS]);
			$descendants = $cached[self::DESCENDANTS];
		}

		foreach ($descendants as $descendantClass => &$descendant) {
			foreach ($descendant as $sourceParam => &$ref) {
				$this->checkCache($ref);
				$ref[] = $this->serviceAccessor->composeIdentifier($descendantClass, $ref[0], $identifier, $sourceParam);
			}
		}
		$map = $this->serviceAccessor->getParamMap($entityClass);
		return $this->createSuggestor($map, $identifier, $suggestions, $descendants, $isContainer);
	}


	/**
	 * @param IParamMap $paramMap
	 * @param IIdentifier $identifier
	 * @param array $suggestions
	 * @param array $descendants
	 * @param bool $isContainer
	 * @return ISuggestor
	 */
	protected function createSuggestor(IParamMap $paramMap, IIdentifier $identifier, array $suggestions, array $descendants = array(), $isContainer = FALSE)
	{
		return new Suggestor($paramMap, $this, $suggestions, $isContainer, $identifier, $descendants);
	}


	private function checkCache($cache)
	{
		if (!is_array($cache)) {
			throw new Exception('Malformed cache. Clear it and try again.');
		}
	}
}
