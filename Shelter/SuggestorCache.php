<?php

namespace Shelter;

class SuggestorCache implements ISuggestorCache
{

	/** @var IExternalCache */
	protected $externalCache;

	/** @var string */
	protected $key;


	/**
	 * @param IExternalCache $cache
	 * @param IRequestKey $requestKey
	 */
	public function __construct(IExternalCache $cache, IRequestKey $requestKey)
	{
		$this->externalCache = $cache;
		$this->key = $requestKey->getKey() . ':';
	}


	/**
	 * @param string $identifier
	 * @param string $paramName
	 * @param IParamMap $map
	 * @return ISuggestor
	 */
	public function cacheParamName($identifier, $paramName, IParamMap $map)
	{
		$key = $this->key . $identifier;
		$cached = $this->externalCache->load($key);
		if (NULL === $cached) {
			$cached = array();
		}

		if (!isset($cached[self::PARAM_NAMES])) {
			$cached[self::PARAM_NAMES] = array();
		}
		if (!in_array($paramName, $cached[self::PARAM_NAMES])) {
			$cached[self::PARAM_NAMES][] = $paramName;
			$this->externalCache->save($key, $cached);
		}
		return $this->createSuggestor($map, $identifier, array($paramName));
	}


	/**
	 * @param string $identifier
	 * @param string $descendantEntityClass
	 * @param string $sourceParam
	 * @return void
	 */
	public function cacheDescendant($identifier, $descendantEntityClass, $sourceParam = NULL)
	{
		$key = $this->key . $identifier;
		$cached = $this->externalCache->load($key);
		if (NULL === $cached) {
			$cached = array();
		}
		if (!isset($cached[self::DESCENDANTS])) {
			$cached[self::DESCENDANTS] = array();
		}

		if (!isset($cached[self::DESCENDANTS][$descendantEntityClass])) {
			$cached[self::DESCENDANTS][$descendantEntityClass] = $sourceParam;
			$this->externalCache->save($key, $cached);
		}
	}


	/**
	 * @param string $identifier
	 * @param IParamMap $map
	 * @return ISuggestor
	 */
	public function getCached($identifier, IParamMap $map)
	{
		$cached = $this->getCached($this->key . $identifier, $map);
		if (NULL === $cached) {
			return NULL;
		}
		$suggestions = isset($cached[self::PARAM_NAMES]) ? $cached[self::PARAM_NAMES] : array();
		$descendants = isset($cached[self::DESCENDANTS]) ? $cached[self::DESCENDANTS] : array();
		return $this->createSuggestor($map, $identifier, $suggestions, $descendants);
	}


	/**
	 * @param IParamMap $paramMap
	 * @param string $identifier
	 * @param array $suggestions
	 * @param array $descendants
	 * @return ISuggestor
	 */
	protected function createSuggestor(IParamMap $paramMap, $identifier, array $suggestions, array $descendants = array())
	{
		// todo: return new Suggestor($paramMap, $this, $suggestions, $identifier, , $descendants);
	}
}
