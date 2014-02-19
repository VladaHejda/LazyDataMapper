<?php

/**
 * OpenObject let you access even protected and private properties and methods.
 * It may be helpful for testing purposes.
 */
class OpenObject
{

	/** @var object */
	protected $_object;

	/** @var bool */
	private $_strict;


	/**
	 * @param object $object to encase
	 * @param bool $strict whether throw an Exception on reading/writing undeclared properties or preserve standard PHP behavior
	 * @throws \InvalidArgumentException
	 */
	public function __construct($object, $strict = FALSE)
	{
		if (!is_object($object)) {
			throw new \InvalidArgumentException(__CLASS__ . ': expected object, ' . gettype($object) . ' given.');
		}
		$this->_object = $object;
		$this->_strict = (bool) $strict;
	}


	/**
	 * @param string $property
	 * @return mixed
	 * @throws \BadMethodCallException
	 */
	public function __get($property)
	{
		if (!property_exists($this->_object, $property)) {
			if (!method_exists($this->_object, '__get')) {
				if ($this->_strict) {
					$class = get_class($this->_object);
					throw new BadMethodCallException("Read an undeclared property $class::\$$property.");
				}
				// invokes notice
				return $this->_object->$property;
			}

			return $this->_object->$property;
		}

		$ref = new \ReflectionProperty($this->_object, $property);
		if ($ref->isPublic()) {
			return $this->_object->$property;
		}

		if (method_exists($this->_object, '__get')) {
			try {
				return $this->_object->$property;
			} catch (\Exception $e) {}
		}

		$ref->setAccessible(TRUE);
		return $ref->getValue($this->_object);
	}


	/**
	 * @param string $property
	 * @param mixed $value
	 * @throws \BadMethodCallException
	 */
	public function __set($property, $value)
	{
		if (!property_exists($this->_object, $property)) {
			if (!method_exists($this->_object, '__set')) {
				if ($this->_strict) {
					$class = get_class($this->_object);
					throw new \BadMethodCallException("Write to an undeclared property $class::\$$property.");
				}
				$this->_object->$property = $value;
				return;
			}

			$this->_object->$property = $value;
			return;
		}

		$ref = new \ReflectionProperty($this->_object, $property);
		if ($ref->isPublic()) {
			$this->_object->$property = $value;
			return;
		}

		$ref->setAccessible(TRUE);

		if (!method_exists($this->_object, '__set')) {
			$ref->setValue($this->_object, $value);
			return;
		}

		$actual = $ref->getValue($this->_object);
		try {
			$this->_object->$property;
		} catch (\Exception $e) {}

		if ($actual === $ref->getValue($this->_object)) {
			// __set() probably does not affected property, set it manually
			$ref->setValue($this->_object, $value);
		}
	}


	/**
	 * @param string $property
	 * @return bool
	 */
	public function __isset($property)
	{
		if (!property_exists($this->_object, $property)) {
			return isset($this->_object->$property);
		}

		$ref = new \ReflectionProperty($this->_object, $property);
		if ($ref->isPublic()) {
			return isset($this->_object->$property);
		}

		if (method_exists($this->_object, '__isset')) {
			try {
				return isset($this->_object->$property);
			} catch (\Exception $e) {}
		}

		$ref->setAccessible(TRUE);
		return NULL !== $ref->getValue($this->_object);
	}


	/**
	 * @param string $property
	 */
	public function __unset($property)
	{
		if (!property_exists($this->_object, $property)) {
			unset($this->_object->$property);
			return;
		}

		$ref = new \ReflectionProperty($this->_object, $property);
		if ($ref->isPublic()) {
			unset($this->_object->$property);
			return;
		}

		$ref->setAccessible(TRUE);

		if (!method_exists($this->_object, '__unset')) {
			$ref->setValue($this->_object, NULL);
			return;
		}

		$actual = $ref->getValue($this->_object);
		try {
			unset($this->_object->$property);
		} catch (\Exception $e) {}

		if ($actual === $ref->getValue($this->_object)) {
			// __unset() probably does not affected property, unset it manually
			$ref->setValue($this->_object, NULL);
		}
	}


	/**
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 * @throws \BadMethodCallException
	 */
	public function __call($method, $args)
	{
		if (!method_exists($this->_object, $method)) {
			if (!method_exists($this->_object, '__call')) {
				$class = get_class($this->_object);
				throw new \BadMethodCallException("Call to undefined method $class::$method().");
			}

			return call_user_func_array([$this->_object, $method], $args);
		}

		$ref = new \ReflectionMethod($this->_object, $method);
		if ($ref->isPublic()) {
			return call_user_func_array([$this->_object, $method], $args);
		}

		if (method_exists($this->_object, '__call')) {
			try {
				return call_user_func_array([$this->_object, $method], $args);
			} catch (\Exception $e) {}
		}

		$ref->setAccessible(TRUE);
		return $ref->invokeArgs($this->_object, $args);
	}
}
