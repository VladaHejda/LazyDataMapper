<?php

namespace LazyDataMapper;

class Hierarchy
{

	/** @var array */
	protected $hierarchy;

	/** @var IIdentifier */
	protected $identifier;


	/**
	 * @param int $id
	 * @param IIdentifier $identifier
	 * @param bool $isContainer
	 * @return static
	 * @throws Exception
	 */
	static function create($id, IIdentifier $identifier, $isContainer = FALSE)
	{
		if (!is_int($id)) {
			throw new Exception('Id must be an integer.');
		}
		return new static(array($id => (bool) $isContainer), $identifier);
	}


	/**
	 * @param array $hierarchy [int $id => bool $isContainer, ...]
	 * @param IIdentifier $identifier
	 */
	private function __construct(array $hierarchy, IIdentifier $identifier)
	{
		$this->hierarchy = $hierarchy;
		$this->identifier = $identifier;
	}


	/**
	 * @return IIdentifier
	 */
	public function getIdentifier()
	{
		return $this->identifier;
	}


	/**
	 * @return int[]
	 */
	public function getIdPath()
	{
		return array_keys($this->hierarchy);
	}


	/**
	 * @return bool[] path of entities (FALSE) and containers (TRUE), represented by booleans
	 */
	public function getZigzag()
	{
		return array_values($this->hierarchy);
	}


	/**
	 * @param $id
	 * @param IIdentifier $identifier
	 * @param bool $isContainer
	 * @return static
	 * @throws Exception
	 */
	public function appendAndCreate($id, IIdentifier $identifier, $isContainer = FALSE)
	{
		if (!is_int($id)) {
			throw new Exception('Id must be an integer.');
		}
		return new static($this->hierarchy + array($id => (bool) $isContainer), $identifier);
	}
}
