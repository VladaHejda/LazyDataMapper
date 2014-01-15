<?php

namespace Shelter;

class Suggestor implements ISuggestor
{

	/** @var IParamMap */
	protected $paramMap;

	/** @var ISuggestorCache */
	protected $cache;

	/** @var array */
	protected $suggestions;

	/** @var string  */
	protected $identifier;

	/** @var array */
	protected $descendants;


	/**
	 * @param IParamMap $paramMap
	 * @param ISuggestorCache $cache
	 * @param array $suggestions
	 * @param string $identifier
	 * @param array $descendants entityClass => identifier
	 */
	public function __construct(IParamMap $paramMap, ISuggestorCache $cache, array $suggestions, $identifier = NULL, array $descendants = array())
	{
		$this->paramMap = $paramMap;
		$this->cache = $cache;
		$this->checkAgainstParamMap($suggestions);
		$this->suggestions = $suggestions;
		$this->identifier = $identifier;
		$this->descendants = $descendants;
	}


	/**
	 * @param string $type
	 * @return bool
	 */
	public function isSuggestedType($type)
	{
		$map = $this->paramMap->getMap($type, TRUE);
		return (bool) array_intersect($this->suggestions, $map);
	}


	/**
	 * @param string $type
	 * @return string[]
	 */
	public function getParamNames($type = NULL)
	{
		if (NULL === $type) {
			return $this->suggestions;
		}

		$map = $this->paramMap->getMap($type, TRUE);
		$suggestions = array();
		foreach ($map as $paramName) {
			if (in_array($paramName, $this->suggestions)) {
				$suggestions[] = $paramName;
			}
		}
		return $suggestions;
	}


	/**
	 * @return string
	 */
	public function getIdentifier()
	{
		return $this->identifier;
	}


	/**
	 * @return bool
	 */
	public function hasDescendants()
	{
		return (bool) $this->descendants;
	}


	/**
	 * @param string $entityClass
	 * @param string $sourceParam reference is modified due to regular source parameter
	 * @return bool
	 * @throws Exception
	 */
	public function hasDescendant($entityClass, &$sourceParam = NULL)
	{
		if (array_key_exists($entityClass, $this->descendants)) {
			if (NULL === $this->descendants[$entityClass]) {
				$sourceParam = NULL;
				return TRUE;
			} elseif (is_array($this->descendants[$entityClass])) {
				if (in_array($sourceParam, $this->descendants[$entityClass])) {
					return TRUE;
				}
			} else {
				throw new Exception('Malformed cache. Clear it and try again.');
			}
		}
		return FALSE;
	}


	/**
	 * @param string $entityClass
	 * @param string $sourceParam
	 * @return self
	 * @throws Exception on unknown descendant
	 */
	public function getDescendant($entityClass, $sourceParam = NULL)
	{
		if (!$this->hasDescendant($entityClass, $sourceParam)) {
			throw new Exception("Descendant $entityClass does not exist.");
		}

		// todo použít serviceAccessor->composeIdentifier(), ovšem zase tu neni service accessor
		$identifier = new Identifier($entityClass, (bool) $sourceParam, $this->identifier, $sourceParam);
		return $this->cache->getCached($identifier->composeIdentifier(), $entityClass);
	}


	/**
	 * @return IParamMap
	 */
	public function getParamMap()
	{
		return $this->paramMap;
	}


	private function checkAgainstParamMap(array $suggestions)
	{
		$map = $this->paramMap->getMap();
		foreach ($suggestions as $paramName) {
			if (!isset($map[$paramName])) {
				throw new Exception("Parameter $paramName is unknown or is not suggested.");
			}
		}
	}
}
