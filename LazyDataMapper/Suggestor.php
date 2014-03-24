<?php

namespace LazyDataMapper;

/**
 * Suggests parameter names and children to Mapper.
 */
class Suggestor implements \Iterator
{

	/** @var ParamMap */
	protected $paramMap;

	/** @var SuggestorCache */
	protected $cache;

	/** @var array */
	protected $suggestions;

	/** @var array */
	protected $children;

	/** @var Hierarchy */
	protected $hierarchy;


	/**
	 * @param ParamMap $paramMap
	 * @param SuggestorCache $cache
	 * @param array $suggestions
	 * @param Hierarchy $hierarchy
	 * @param array $children entityClass => IIdentifier
	 * @throws Exception
	 */
	public function __construct(ParamMap $paramMap, SuggestorCache $cache, array $suggestions, Hierarchy $hierarchy = NULL, array $children = array())
	{
		$this->paramMap = $paramMap;
		$this->cache = $cache;
		$this->checkAgainstParamMap($suggestions);
		$this->suggestions = $suggestions;
		$this->children = $children;
		$this->hierarchy = $hierarchy;
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
	 */
	public function getSuggestions($group = NULL)
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
		if ($this->hierarchy) {
			$this->hierarchy->getIdentifier();
		}
		return NULL;
	}


	/**
	 * @return Hierarchy
	 */
	public function getHierarchy()
	{
		return $this->hierarchy;
	}


	/**
	 * Says whether has at least one child.
	 * @return bool
	 */
	public function hasChildren()
	{
		$this->rewind();
		return $this->valid();
	}


	/**
	 * @param string $sourceParam
	 * @return self|null returns NULL when child does not exist
	 * @throws Exception
	 */
	public function getChild($sourceParam)
	{
		if (!array_key_exists($sourceParam, $this->children)) {
			return NULL;
		}

		if ($this->children[$sourceParam] instanceof self) {
			return $this->children[$sourceParam];
		}

		list($entityClass, $hierarchy, $identifier) = $this->children[$sourceParam];

		$child = $this->loadChild($identifier, $entityClass, $hierarchy);
		if (!$child) {
			unset($this->children[$sourceParam]);
			return NULL;
		}

		$this->children[$sourceParam] = $child;
		return $child;
	}


	/**
	 * @see getChild()
	 */
	public function __get($sourceParam)
	{
		return $this->getChild($sourceParam);
	}


	public function rewind()
	{
		reset($this->children);
	}


	public function valid()
	{
		$current = current($this->children);

		if (FALSE === $current) {
			return FALSE;
		}
		if ($current instanceof self) {
			return TRUE;
		}

		list($entityClass, $hierarchy, $identifier) = $current;
		$child = $this->loadChild($identifier, $entityClass, $hierarchy);
		$key = key($this->children);

		// child have nothing cached
		if (!$child) {
			unset($this->children[$key]);
			return $this->valid();
		}

		$this->children[$key] = $child;
		return TRUE;
	}


	public function current()
	{
		$current = current($this->children);

		if ($current instanceof self) {
			return $current;
		}

		// when method called individually
		if ($this->valid()) {
			return current($this->children);
		}

		return FALSE;
	}


	public function key()
	{
		if (current($this->children) instanceof self || $this->valid()) {
			return key($this->children);
		}

		return FALSE;
	}


	public function next()
	{
		next($this->children);
	}


	/**
	 * @return ParamMap
	 */
	public function getParamMap()
	{
		return $this->paramMap;
	}


	protected function loadChild(IIdentifier $identifier, $entityClass, $hierarchy)
	{
		return $this->cache->getCached($identifier, $entityClass, $hierarchy);
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
