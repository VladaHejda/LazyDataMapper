<?php

namespace Shelter;

abstract class Restrictor implements IRestrictor
{

	const DENY = ':deny';

	const UNION = 0,
		REPLACE = 1;

	/** @var array */
	private $limit = array(
		'range' => array(),
		'equal' => array(),
		'unequal' => array(),
	);


	/**
	 * @param string $paramName
	 * @param mixed $values
	 * @param int $composition
	 * @throws Exception
	 */
	protected function equals($paramName, $values, $composition = self::UNION)
	{
		if ($values === self::DENY) {
			unset($this->limit['equal'][$paramName]);
			return;
		}

		if (isset($this->limit['unequal'][$paramName])) {
			throw new Exception(get_class($this) . ': only equals or notEquals can be used, not both.');
		}

		$this->compose($this->limit['equal'], $paramName, $values, $composition);
	}


	/**
	 * @param string $paramName
	 * @param mixed $values
	 * @param int $composition
	 * @throws Exception
	 */
	protected function notEquals($paramName, $values, $composition = self::UNION)
	{
		if ($values === self::DENY) {
			unset($this->limit['unequal'][$paramName]);
			return;
		}

		if (isset($this->limit['equal'][$paramName])) {
			throw new Exception(get_class($this) . ': only equals or notEquals can be used, not both.');
		}

		$this->compose($this->limit['unequal'], $paramName, $values, $composition);
	}


	/**
	 * @param string $paramName
	 * @param null|mixed $min
	 * @param null|mixed $max
	 * @throws Exception
	 */
	protected function inRange($paramName, $min,  $max = NULL)
	{
		if (NULL === $min && NULL === $max) {
			throw new Exception(get_class($this) . ": at least min or max must be defined.");
		}

		if (NULL !== $min && NULL !== $max && $min > $max) {
			// swap values
			$tmp = $max;
			$max = $min;
			$min = $tmp;
		}

		$this->limit['range'][$paramName] = array($min, $max);
	}


	protected function getEqual($paramName = NULL)
	{
		if (isset($this->limit['equal'])) {
			if (NULL === $paramName) {
				return $this->limit['equal'];
			}

			if (!array_key_exists($paramName, $this->limit['equal'])) {
				return array();
			}

			return $this->limit['equal'][$paramName];

		} else {
			return array();
		}
	}


	protected function getUnequal($paramName = NULL)
	{
		if (isset($this->limit['unequal'])) {
			if (NULL === $paramName) {
				return $this->limit['unequal'];
			}

			if (!array_key_exists($paramName, $this->limit['unequal'])) {
				return array();
			}

			return $this->limit['unequal'][$paramName];

		} else {
			return array();
		}
	}


	protected function getRange($paramName = NULL)
	{
		if (isset($this->limit['range'])) {
			if (NULL === $paramName) {
				return $this->limit['range'];
			}

			if (!array_key_exists($paramName, $this->limit['range'])) {
				return array();
			}

			return $this->limit['range'][$paramName];

		} else {
			return array();
		}
	}


	private function compose(& $medium, $part, $values, $type)
	{
		// todo co při $values = array() ?
		$values = is_array($values) ? $values : array($values);

		if (!isset($medium[$part]) || $type === self::REPLACE) {
			$medium[$part] = $values;

		} elseif ($type === self::UNION) {
			$medium[$part] = array_merge($medium[$part], $values);

		} else {
			throw new Exception(get_class($this) . ": unknown composition type $type.");
		}
	}
}
