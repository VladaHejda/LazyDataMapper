<?php

namespace LazyDataMapper;

class Suggestor implements ISuggestor
{

	/** @var IParamMap */
	protected $paramMap;

	/** @var ISuggestorCache */
	protected $cache;

	/** @var array */
	protected $suggestions;

	/** @var IIdentifier  */
	protected $identifier;

	/** @var array */
	protected $descendants;

	/** @var bool */
	protected $isContainer;

	/** @var int */
	private $pos = 0;

	/** @var self */
	private $currentDescendant;


	/**
	 * @param IParamMap $paramMap
	 * @param ISuggestorCache $cache
	 * @param array $suggestions
	 * @param bool $isContainer
	 * @param IIdentifier $identifier
	 * @param array $descendants entityClass => IIdentifier
	 */
	public function __construct(IParamMap $paramMap, ISuggestorCache $cache, array $suggestions, $isContainer = FALSE, IIdentifier $identifier = NULL, array $descendants = array())
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
	 * @param string $group
	 * @return string[]
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
	 * @return bool
	 */
	public function hasDescendants()
	{
		$this->rewind();
		return $this->valid();
	}


	/**
	 * @param string $entityClass
	 * @param string $sourceParam if there is only one descendant of given class, source parameter can be omitted,
	 *      then argument reference is set to regular source parameter
	 * @return bool
	 * @throws Exception
	 */
	public function hasDescendant($entityClass, &$sourceParam = NULL)
	{
		if (!isset($this->descendants[$entityClass])) {
			return FALSE;
		}

		if (NULL === $sourceParam) {
			if (count($this->descendants[$entityClass]) > 1) {
				throw new Exception("Descendant $entityClass is ambiguous.");
			}
			reset($this->descendants[$entityClass]);
			$sourceParam = key($this->descendants[$entityClass]);

		} elseif (!array_key_exists($sourceParam, $this->descendants[$entityClass])) {
			return FALSE;
		}

		return TRUE;
	}


	/**
	 * @param string $entityClass
	 * @param string $sourceParam if there is only one descendant of given class, source parameter can be omitted,
	 *      then argument reference is set to regular source parameter
	 * @return self|null returns NULL when descendant does not exist
	 * @throws Exception
	 */
	public function getDescendant($entityClass, &$sourceParam = NULL)
	{
		if (!isset($this->descendants[$entityClass])) {
			return NULL;
		}

		if (NULL === $sourceParam) {
			if (count($this->descendants[$entityClass]) > 1) {
				throw new Exception("Descendant $entityClass is ambiguous.");
			}
			list($isContainer, $identifier) = reset($this->descendants[$entityClass]);
			$sourceParam = key($this->descendants[$entityClass]);

		} elseif (array_key_exists($sourceParam, $this->descendants[$entityClass])) {
			list($isContainer, $identifier) = $this->descendants[$entityClass][$sourceParam];

		} else {
			return NULL;
		}

		return $this->loadDescendant($identifier, $entityClass, $isContainer);
	}


	/**
	 * @param string $entityClass
	 * @param string $sourceParam
	 * @return IIdentifier
	 * @throws Exception when descendant does not exist
	 */
	public function getDescendantIdentifier($entityClass, $sourceParam)
	{
		if (!isset($this->descendants[$entityClass])) {
			throw new Exception("No descendant of class $entityClass.");
		}

		if (!array_key_exists($sourceParam, $this->descendants[$entityClass])) {
			throw new Exception("Descendant $entityClass on source parameter $sourceParam does not exist.");
		}

		return $this->descendants[$entityClass][$sourceParam][1];
	}


	public function rewind()
	{
		foreach ($this->descendants as &$descendant) {
			reset($descendant);
		}
		reset($this->descendants);

		$this->pos = 0;
	}


	public function valid()
	{
		++$this->pos;

		if (FALSE === current($this->descendants)) {
			return FALSE;
		}
		$entityClass = key($this->descendants);
		$descendant = current($this->descendants[$entityClass]);
		list($isContainer, $identifier) = $descendant;

		$this->currentDescendant = $this->loadDescendant($identifier, $entityClass, $isContainer);
		if (!$this->currentDescendant) {
			$this->next();
			return $this->valid();
		}

		return TRUE;
	}


	public function current()
	{
		return $this->currentDescendant;
	}


	public function key()
	{
		return $this->pos;
	}


	public function next()
	{
		$entityClass = key($this->descendants);
		next($this->descendants[$entityClass]);
		if (FALSE === current($this->descendants[$entityClass])) {
			next($this->descendants);
		}
	}


	/**
	 * @return IParamMap
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
