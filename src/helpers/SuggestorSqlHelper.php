<?php

namespace LazyDataMapper;

class SuggestorSqlHelper
{

	/** @var array */
	public static $reservedWords = array();

	/** @var string */
	public static $aliasNotation = '%s AS %s';

	/** @var bool */
	public static $wrapEverything = FALSE;

	/** @var string */
	public static $leftWrapper = '`';
	public static $rightWrapper = '`';

	/** @var string */
	protected $path;

	/** @var string */
	protected $group;

	/** @var array */
	protected $aliases = array();

	/** @var string */
	protected $tableAlias;

	/** @var Suggestor */
	private $suggestor;

	/** @var array */
	private $suggestions;


	/**
	 * @param Suggestor $suggestor
	 */
	public function __construct(Suggestor $suggestor)
	{
		$this->suggestor = $suggestor;
	}


	/**
	 * @return string
	 */
	public function build()
	{
		return implode(', ', $this->getRaw());
	}


	/**
	 * @return array
	 */
	public function getRaw()
	{
		$suggestions = $this->getSuggestions();

		foreach ($suggestions as &$suggestion) {
			// reserved word
			if (static::$wrapEverything || in_array($suggestion, static::$reservedWords)) {
				$suggestion = $this->wrap($suggestion);
			}

			// conflict
			if (isset($this->aliases[$suggestion])) {
				$suggestion = sprintf(static::$aliasNotation, $suggestion, $this->aliases[$suggestion]);
			}

			// table alias
			if ($this->tableAlias !== NULL) {
				$suggestion = "$this->tableAlias.$suggestion";
			}
		}

		return $suggestions;
	}


	/**
	 * @param string $path
	 * @return static
	 * @throws Exception
	 */
	public function setPath($path)
	{
		if ($this->path !== NULL) {
			throw new Exception("Path already set to '$this->path'.");
		}
		$this->path = $path;
		return $this;
	}


	/**
	 * @param string $group
	 * @return static
	 * @throws Exception
	 */
	public function setGroup($group)
	{
		if ($this->group !== NULL) {
			throw new Exception("Group already set to '$this->group'.");
		}
		$this->group = $group;
		return $this;
	}


	/**
	 * @param array $conflicts
	 * @return static
	 * @throws Exception
	 */
	public function addConflicts(array $conflicts)
	{
		if ($diff = array_diff(array_keys($conflicts), $this->getSuggestions())) {
			$diff = implode("', '", $diff);
			throw new Exception("There is no suggestion '$diff' in Suggestor.");
		}
		$this->aliases = $conflicts + $this->aliases;
		return $this;
	}


	/**
	 * @param string $alias
	 * @return static
	 */
	public function setTableAlias($alias)
	{
		$this->tableAlias = $alias;
		return $this;
	}


	/**
	 * @param DataHolder $holder
	 * @param array|\Traversable $data
	 * @return DataHolder
	 * @throws Exception
	 */
	public function completeDataHolder(DataHolder $holder, $data)
	{
		if ($this->suggestor !== $holder->getSuggestor()) {
			throw new Exception('Given DataHolder is not related with base Suggestor.');
		}
		$holder = $this->getSourceDataHolder($holder);

		// throwing an Exception resolves DataHolder::setData()
		if (is_array($data) || $data instanceof \Traversable) {
			if ($holder->getSuggestor()->isCollection()) {
				foreach ($data as &$subdata) {
					if (!is_array($subdata) && !$subdata instanceof \Traversable) {
						continue;
					}
					$subdata = $this->resolveConflicts($subdata);
				}
			} else {
				$data = $this->resolveConflicts($data);
			}
		}

		return $holder->setData($data);
	}


	/**
	 * @return Suggestor
	 * @throws Exception
	 */
	final public function getSourceSuggestor()
	{
		$sourceSuggestor = $this->suggestor;
		if ($this->path !== NULL) {
			foreach (explode('.', $this->path) as $child) {
				$sourceSuggestor = $sourceSuggestor->$child;

				if ($sourceSuggestor === NULL) {
					throw new Exception("No suggestor child on path '$this->path'.");
				}
			}
		}
		return $sourceSuggestor;
	}


	/**
	 * @param string $wrapper one or two chars long string
	 * @throws Exception
	 */
	public static function setWordWrapper($wrapper)
	{
		$length = strlen($wrapper);
		if ($length === 1) {
			static::$leftWrapper = static::$rightWrapper = $wrapper;
		} elseif ($length === 2) {
			static::$leftWrapper = $wrapper[0];
			static::$rightWrapper = $wrapper[1];
		} else {
			throw new Exception("Wrong wrapper '$wrapper'.");
		}
	}


	/**
	 * @param string $word
	 * @return string
	 */
	protected function wrap($word)
	{
		return static::$leftWrapper . $word . static::$rightWrapper;
	}


	final protected function getSuggestions()
	{
		if ($this->suggestions === NULL) {
			$this->suggestions = $this->getSourceSuggestor()->getSuggestions($this->group);
		}
		return $this->suggestions;
	}


	/**
	 * @param DataHolder $holder
	 * @return DataHolder
	 */
	protected function getSourceDataHolder(DataHolder $holder)
	{
		if ($this->path !== NULL) {
			foreach (explode('.', $this->path) as $child) {
				$holder = $holder->$child;
			}
		}
		return $holder;
	}


	/**
	 * @param array $data
	 * @return array|\Traversable
	 * @throws Exception
	 */
	protected function resolveConflicts($data)
	{
		foreach ($this->aliases as $conflict => $alias) {
			if (!array_key_exists($alias, $data)) {
				throw new Exception("Missing alias parameter '$alias' in data.");
			}
			$data[$conflict] = $data[$alias];
			if ($conflict != $alias) {
				unset($data[$alias]);
			}
		}
		return $data;
	}
}
