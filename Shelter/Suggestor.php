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

	/** @var int */
	private $pos = 0;

	/** @var self */
	private $currentDescendant;


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
		$this->rewind();
		return $this->valid();
	}


	// todo hasDescendant($class, $source) - as well as into DataHolder (for better manipulation in Mappers)


	/**
	 * @param string $entityClass
	 * @param string $sourceParam if there is only one descendant of given class, source parameter can be omitted,
	 *      then argument reference is set to regular source parameter
	 * @return self|null returns NULL when descendant does not exist
	 * @throws Exception
	 */
	public function getDescendant($entityClass, &$sourceParam = NULL)
	{
		if (isset($this->descendants[$entityClass])) {
			if (!is_array($this->descendants[$entityClass])) {
				throw new Exception('Malformed cache. Clear it and try again.');
			}

			if (NULL === $sourceParam) {
				if (count($this->descendants) > 1) {
					throw new Exception("Descendant $entityClass is ambiguous.");
				}
				reset($this->descendants);
				$sourceParam = key($this->descendants);
			}

			if (!in_array($sourceParam, $this->descendants[$entityClass])) {
				return NULL;
			}

			return $this->loadDescendant($entityClass, $sourceParam);
		}
		return NULL;
	}


	public function rewind()
	{
		foreach ($this->descendants as &$descendant) {
			if (!is_array($descendant)) {
				throw new Exception('Malformed cache. Clear it and try again.');
			}
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
		$sourceParam = current($this->descendants[$entityClass]);

		$this->currentDescendant = $this->loadDescendant($entityClass, $sourceParam);
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


	protected function loadDescendant($entityClass, $sourceParam)
	{
		// todo použít serviceAccessor->composeIdentifier(), ovšem zase tu neni service accessor
		$identifier = new Identifier($entityClass, (bool) $sourceParam, $this->identifier, $sourceParam);

		return $this->cache->getCached($identifier->composeIdentifier(), $entityClass);
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
