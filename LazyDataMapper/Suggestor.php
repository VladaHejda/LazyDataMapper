<?php

namespace LazyDataMapper;

/**
 * Suggests parameter names and descendants to Mapper.
 */
class Suggestor implements \Iterator
{

	/** @var ParamMap */
	protected $paramMap;

	/** @var SuggestorCache */
	protected $cache;

	/** @var array */
	protected $suggestions;

	/** @var IIdentifier  */
	protected $identifier;

	/** @var array */
	protected $descendants;

	/** @var bool */
	protected $isContainer;


	/**
	 * @param ParamMap $paramMap
	 * @param SuggestorCache $cache
	 * @param array $suggestions
	 * @param bool $isContainer
	 * @param IIdentifier $identifier
	 * @param array $descendants entityClass => IIdentifier
	 */
	public function __construct(ParamMap $paramMap, SuggestorCache $cache, array $suggestions, $isContainer = FALSE, IIdentifier $identifier = NULL, array $descendants = array())
	{
		$this->paramMap = $paramMap;
		$this->cache = $cache;
		$this->checkAgainstParamMap($suggestions);
		$this->suggestions = $suggestions;
		$this->identifier = $identifier;
		$this->descendants = $descendants;
		$this->isContainer = $isContainer;
	}


	/**
	 * @param string $group
	 * @return bool
	 */
	public function isSuggestedGroup($group)
	{
		$map = $this->paramMap->getMap($group, FALSE);
		return (bool) array_intersect($this->suggestions, $map);
	}


	/**
	 * If grouped but group is omitted, it returns all param names merged.
	 * @param string $group
	 * @return string[]
	 * @todo rename to getSuggestions() ?
	 */
	public function getParamNames($group = NULL)
	{
		if (NULL === $group) {
			return $this->suggestions;
		}

		$map = $this->paramMap->getMap($group, FALSE);
		$suggestions = array();
		foreach ($map as $paramName) {
			if (in_array($paramName, $this->suggestions)) {
				$suggestions[] = $paramName;
			}
		}
		return $suggestions;
	}


	/**
	 * @return IIdentifier
	 */
	public function getIdentifier()
	{
		return $this->identifier;
	}


	/**
	 * @return bool
	 */
	public function isContainer()
	{
		return $this->isContainer;
	}


	/**
	 * Says whether has at least one descendant.
	 * @return bool
	 */
	public function hasDescendants()
	{
		$this->rewind();
		return $this->valid();
	}


	/**
	 * @param string $sourceParam
	 * @return self|null returns NULL when descendant does not exist
	 * @throws Exception
	 */
	public function getDescendant($sourceParam)
	{
		if (!array_key_exists($sourceParam, $this->descendants)) {
			return NULL;
		}

		if ($this->descendants[$sourceParam] instanceof self) {
			return $this->descendants[$sourceParam];
		}

		list($entityClass, $isContainer, $identifier) = $this->descendants[$sourceParam];

		$descendant = $this->loadDescendant($identifier, $entityClass, $isContainer);
		if (!$descendant) {
			unset($this->descendants[$sourceParam]);
			return NULL;
		}

		$this->descendants[$sourceParam] = $descendant;
		return $descendant;
	}


	/**
	 * @see getDescendant()
	 */
	public function __get($sourceParam)
	{
		return $this->getDescendant($sourceParam);
	}


	public function rewind()
	{
		reset($this->descendants);
	}


	public function valid()
	{
		$current = current($this->descendants);

		if (FALSE === $current) {
			return FALSE;
		}
		if ($current instanceof self) {
			return TRUE;
		}

		list($entityClass, $isContainer, $identifier) = $current;
		$descendant = $this->loadDescendant($identifier, $entityClass, $isContainer);
		$key = key($this->descendants);

		// descendant have nothing cached
		if (!$descendant) {
			unset($this->descendants[$key]);
			return $this->valid();
		}

		$this->descendants[$key] = $descendant;
		return TRUE;
	}


	public function current()
	{
		$current = current($this->descendants);

		if ($current instanceof self) {
			return $current;
		}

		// when method called individually
		if ($this->valid()) {
			return current($this->descendants);
		}

		return FALSE;
	}


	public function key()
	{
		if (current($this->descendants) instanceof self || $this->valid()) {
			return key($this->descendants);
		}

		return FALSE;
	}


	public function next()
	{
		next($this->descendants);
	}


	/**
	 * @return ParamMap
	 */
	public function getParamMap()
	{
		return $this->paramMap;
	}


	protected function loadDescendant(IIdentifier $identifier, $entityClass, $isContainer)
	{
		return $this->cache->getCached($identifier, $entityClass, $isContainer);
	}


	private function checkAgainstParamMap(array $suggestions)
	{
		foreach ($suggestions as $paramName) {
			if (!$this->paramMap->hasParam($paramName)) {
				throw new Exception("Parameter $paramName is unknown.");
			}
		}
	}
}
