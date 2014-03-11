<?php

namespace LazyDataMapper;

abstract class ParamMap implements IParamMap
{

	/** @var array set this map in descendant */
	protected $map = array();

	/** @var array optional default values of parameters */
	protected $default = array();

	/** @var bool */
	private $grouped;


	public function __construct()
	{
		foreach ($this->map as $map) {
			if (NULL === $this->grouped) {
				$this->grouped = is_array($map);
				continue;
			}

			if ($this->grouped && !is_array($map)) {
				throw new Exception(get_class($this).": map is defective.");
			}
		}
	}


	/**
	 * @param string $group
	 * @param bool $flip
	 * @return array
	 * @throws Exception
	 */
	public function getMap($group = NULL, $flip = TRUE)
	{
		if (NULl === $group) {
			if (!$flip) {
				return $this->map;
			}
			if (!$this->grouped) {
				return array_fill_keys($this->map, NULL);
			}
			$groups = $this->map;
			foreach ($groups as &$map) {
				$map = array_fill_keys($map, NULL);
			}
			return $groups;

		} else {
			if (!$this->grouped) {
				throw new Exception(get_class($this).": map is not grouped, nevertheless group $group required.");
			}
			if (!isset($this->map[$group])) {
				throw new Exception(get_class($this).": unknown group $group.");
			}

			if (!$flip) {
				return $this->map[$group];
			}
			return array_fill_keys($this->map[$group], NULL);
		}
	}


	/**
	 * @return bool
	 */
	public function isGrouped()
	{
		return $this->grouped;
	}


	/**
	 * @param string $paramName
	 * @return bool
	 */
	public function hasParam($paramName)
	{
		if ($this->grouped) {
			foreach ($this->map as $map) {
				if (in_array($paramName, $map)) {
					return TRUE;
				}
			}
			return FALSE;
		}

		return in_array($paramName, $this->map);
	}


	/**
	 * @param string $group
	 * @return bool
	 * @throws Exception
	 */
	public function hasGroup($group)
	{
		if (!$this->grouped) {
			throw new Exception(get_class($this).": map is not grouped.");
		}

		return isset($this->map[$group]);
	}


	/**
	 * @param string $paramName
	 * @return string
	 * @throws Exception
	 */
	public function getParamGroup($paramName)
	{
		if (!$this->grouped) {
			throw new Exception(get_class($this).": map is not grouped.");
		}

		foreach ($this->map as $group => $map) {
			if (in_array($paramName, $map)) {
				return $group;
			}
		}
		throw new Exception(get_class($this).": unknown parameter name $paramName.");
	}


	/**
	 * @param string $paramName
	 * @return mixed
	 * @throws Exception
	 */
	public function getDefaultValue($paramName)
	{
		if (!$this->hasParam($paramName)) {
			throw new Exception(get_class($this).": unknown parameter name $paramName.");
		}

		if (array_key_exists($paramName, $this->default)) {
			return $this->default[$paramName];
		}
		return NULL;
	}
}
