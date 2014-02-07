<?php

namespace Shelter;

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
		}

		if (!isset($cached[self::PARAM_NAMES])) {
			$cached[self::PARAM_NAMES] = array();
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
	 * @return void
	 */
	public function cacheDescendant(IIdentifier $identifier, $descendantEntityClass, $sourceParam)
	{
		$key = $this->key . $identifier->getKey();
		$cached = $this->externalCache->load($key);

		if (NULL === $cached) {
			$cached = array();
		}
		if (!isset($cached[self::DESCENDANTS])) {
			$cached[self::DESCENDANTS] = array();
		}
		$cachedShortcut = & $cached[self::DESCENDANTS];
		if (!isset($cachedShortcut[$descendantEntityClass])) {
			$cachedShortcut[$descendantEntityClass] = array();
		}
		$cachedShortcut = & $cachedShortcut[$descendantEntityClass];
		if (array_key_exists($sourceParam, $cachedShortcut)) {
			return;
		}
		$cachedShortcut[$sourceParam] = TRUE;
		$this->externalCache->save($key, $cached);
	}


	/**
	 * @param IIdentifier $identifier
	 * @param string $entityClass
	 * @return ISuggestor
	 */
	public function getCached(IIdentifier $identifier, $entityClass)
	{
		$cached = $this->externalCache->load($this->key . $identifier->getKey());
		if (NULL === $cached) {
			return NULL;
		}
		$suggestions = isset($cached[self::PARAM_NAMES]) ? $cached[self::PARAM_NAMES] : array();
		$descendants = isset($cached[self::DESCENDANTS]) ? $cached[self::DESCENDANTS] : array();
		foreach ($descendants as $descendantClass => &$descendant) {
			foreach ($descendant as $sourceParam => &$ref) {
				$ref = $this->serviceAccessor->composeIdentifier($descendantClass, FALSE, $identifier, $sourceParam);
			}
		}
		$map = $this->serviceAccessor->getParamMap($entityClass);
		return $this->createSuggestor($map, $identifier, $suggestions, $descendants);
	}


	/**
	 * @param IParamMap $paramMap
	 * @param IIdentifier $identifier
	 * @param array $suggestions
	 * @param array $descendants
	 * @return ISuggestor
	 */
	protected function createSuggestor(IParamMap $paramMap, IIdentifier $identifier, array $suggestions, array $descendants = array())
	{
		return new Suggestor($paramMap, $this, $suggestions, $identifier,  $descendants);
	}
}
