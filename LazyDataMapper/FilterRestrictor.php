<?php

namespace LazyDataMapper;

/**
 * FilterRestrictor creates closure that says whether given data pass restrictions.
 */
class FilterRestrictor extends Restrictor
{

	/**
	 * @return callable
	 */
	public function getRestrictions()
	{
		return function ($data) {

			foreach ($this->getEqual() as $paramName => $limit) {
				if (!array_key_exists($paramName, $data)) {
					throw new Exception("Unknown param name $paramName in data.");
				}

				if (!in_array($data[$paramName], $limit)) {
					return FALSE;
				}
			}

			foreach ($this->getUnequal() as $paramName => $limit) {
				if (!array_key_exists($paramName, $data)) {
					throw new Exception("Unknown param name $paramName in data.");
				}

				if (in_array($data[$paramName], $limit)) {
					return FALSE;
				}
			}

			foreach ($this->getRange() as $paramName => $limit) {
				if (!array_key_exists($paramName, $data)) {
					throw new Exception("Unknown param name $paramName in data.");
				}

				list($min, $max) = $limit;
				if (NULL !== $min && $data[$paramName] < $min) {
					return FALSE;
				}
				if (NULL !== $max && $data[$paramName] > $max) {
					return FALSE;
				}
			}

			foreach ($this->getMatch() as $paramName => $pattern) {
				if (!array_key_exists($paramName, $data)) {
					throw new Exception("Unknown param name $paramName in data.");
				}

				if (!preg_match($pattern, $data[$paramName])) {
					return FALSE;
				}
			}

			foreach ($this->getNotMatch() as $paramName => $pattern) {
				if (!array_key_exists($paramName, $data)) {
					throw new Exception("Unknown param name $paramName in data.");
				}

				if (preg_match($pattern, $data[$paramName])) {
					return FALSE;
				}
			}

			return TRUE;
		};
	}
}
