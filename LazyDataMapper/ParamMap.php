<?php

namespace LazyDataMapper;

// todo not typed, but GROUPED! :)
abstract class ParamMap implements IParamMap
{

	/** @var array set this map in descendant */
	protected $map = array();

	/** @var array optional default values of parameters */
	protected $default = array();

	/** @var bool */
	private $separatedByType;


	public function __construct()
	{
		foreach ($this->map as $map) {
			if (NULL === $this->separatedByType) {
				$this->separatedByType = is_array($map);
				continue;
			}

			if ($this->separatedByType && !is_array($map)) {
				throw new Exception(get_class($this).": map is defective.");
			}
		}
	}


	/**
	 * @param string $type
	 * @param bool $flip
	 * @return array
	 * @throws Exception
	 */
	public function getMap($type = NULL, $flip = TRUE)
	{
		if (NULl === $type) {
			if (!$flip) {
				return $this->map;
			}
			if (!$this->separatedByType) {
				return array_fill_keys($this->map, NULL);
			}
			$types = $this->map;
			foreach ($types as &$map) {
				$map = array_fill_keys($map, NULL);
			}
			return $types;

		} else {
			if (!$this->separatedByType) {
				throw new Exception(get_class($this).": map is not separated by type, nevertheless type $type required.");
			}
			if (!isset($this->map[$type])) {
				throw new Exception(get_class($this).": unknown type $type.");
			}

			if (!$flip) {
				return $this->map[$type];
			}
			return array_fill_keys($this->map[$type], NULL);
		}
	}


	/**
	 * @return bool
	 */
	public function isSeparatedByType()
	{
		return $this->separatedByType;
	}


	/**
	 * @param string $paramName
	 * @return bool
	 */
	public function hasParam($paramName)
	{
		if ($this->separatedByType) {
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
	 * @param string $type
	 * @return bool
	 * @throws Exception
	 */
	public function hasType($type)
	{
		if (!$this->separatedByType) {
			throw new Exception(get_class($this).": map is not separated by type.");
		}

		return isset($this->map[$type]);
	}


	/**
	 * @param string $paramName
	 * @return string
	 * @throws Exception
	 */
	public function getParamType($paramName)
	{
		if (!$this->separatedByType) {
			throw new Exception(get_class($this).": map is not separated by type.");
		}

		foreach ($this->map as $type => $map) {
			if (in_array($paramName, $map)) {
				return $type;
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
