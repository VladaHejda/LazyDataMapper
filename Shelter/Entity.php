<?php

namespace Shelter;

abstract class Entity implements IEntity
{

	/** @var int */
	protected $id;

	/** @var IIdentifier */
	protected $identifier;

	/** @var IAccessor */
	protected $accessor;

	/** @var array list of private param names */
	protected $privateParams = array();

	/** @var bool give TRUE to activate paramNames translation, see self::translate() */
	protected $translate = FALSE;

	/** @var array */
	private $params;

	/** @var array */
	private $wrappedParams = array();

	/** @var array */
	private $originalParams = array();

	/** @var array current got param */
	private $getting = array();

	/** @var array param => array of dependent params */
	private $dependencies = array();

	/** @var array classes getters and setters */
	private static $IO = array();


	/**
	 * @param int $id
	 * @param array $params
	 * @param IIdentifier $identifier
	 * @param IAccessor $accessor
	 */
	public function __construct($id, array $params, IIdentifier $identifier, IAccessor $accessor)
	{
		$this->id = (int) $id;
		$this->params = $params;
		$this->identifier = $identifier;
		$this->accessor = $accessor;
	}


	/**
	 * @return IIdentifier
	 */
	public function getIdentifier()
	{
		return $this->identifier;
	}


	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * @param string $param
	 * @return mixed wrapped param
	 * @throws EntityException when read undeclared / private param
	 */
	public function __get($param)
	{
		$param[0] = strtolower($param[0]);
		$param = $this->translateParamName($param);

		// dependencies on param
		if ($this->getting) {
			$this->writeDependency($param, end($this->getting));
		}

		// param already wrapped
		if (array_key_exists($param, $this->wrappedParams)) {
			return $this->wrappedParams[$param];
		}

		$hasClear = $this->hasClear($param, $isLazy);
		$this->hasWrapper($param, $wrapper);

		// undeclared / private param
		if ((!$wrapper && !$hasClear) || $this->isPrivate($param)) {
			throw new EntityException(get_class($this) . ": Cannot read an undeclared parameter $param.", EntityException::READ_UNDECLARED);
		}

		// wrapper
		if ($wrapper) {
			if ($hasClear) {
				$clear = $this->getClear($param);
			}
			$this->getting[] = $param;
			$this->wrappedParams[$param] = $hasClear ? $this->$wrapper($clear) : $this->$wrapper();

			array_pop($this->getting);
			return $this->wrappedParams[$param];
		}

		if ($isLazy) {
			$this->params[$param] = $this->lazyLoad($param);
		}

		// clear param
		return $this->wrappedParams[$param] = $this->params[$param];
	}


	/**
	 * @param string $param
	 * @param array $args
	 * @return mixed wrapped param
	 * @throws EntityException when read undeclared / private param
	 */
	public function __call($param, $args)
	{
		// property alias
		if (empty($args)) {
			return $this->$param;
		}

		// no wrapper, property alias
		if (!$this->hasWrapper($param, $wrapper)) {
			return $this->$param;
		}

		$param[0] = strtolower($param[0]);
		$param = $this->translateParamName($param);

		$hasClear = $this->hasClear($param, $isLazy);
		if ($isLazy) {
			$this->params[$param] = $this->lazyLoad($param);
		}

		// wrapper with arguments
		if ($hasClear) {
			array_unshift($args, $this->params[$param]);
		}
		return call_user_func_array(array($this, $wrapper), $args);
	}


	/**
	 * Says whether non-private param exists (does not matter what value param has)
	 * @param string $param
	 * @return bool
	 */
	public function __isset($param)
	{
		$param[0] = strtolower($param[0]);
		$param = $this->translateParamName($param);

		return ($this->hasClear($param) && !$this->isPrivate($param)) || $this->hasWrapper($param);
	}


	/**
	 * Says whether param is read only.
	 * @param string $param
	 * @return bool
	 * @throws EntityException on undeclared / private param
	 */
	final public function isReadOnly($param)
	{
		if (isset($this->$param)) {
			return !$this->hasUnwrapper($param);
		}

		throw new EntityException(get_class($this) . ": Cannot read an undeclared parameter $param.", EntityException::READ_UNDECLARED);
	}


	/**
	 * @param string $param
	 * @param mixed $value
	 * @throws EntityException when set undeclared / read-only / private param
	 */
	public function __set($param, $value)
	{
		$this->setVar($param, $value, TRUE);
	}


	/**
	 * Says whether param is changed including private and read-only params.
	 * @param string $param
	 * @return bool
	 * @throws EntityException on undeclared param
	 */
	final public function isChanged($param = NULL)
	{
		// at least one change
		if (is_null($param)) {
			return (bool) $this->originalParams;
		}

		$param[0] = strtolower($param[0]);
		$param = $this->translateParamName($param);

		// change detected
		if (array_key_exists($param, $this->originalParams)) {
			return TRUE;
		}

		$hasClear = $this->hasClear($param);

		// undeclared param
		if (!$hasClear && !$this->hasWrapper($param)) {
			throw new EntityException(get_class($this) . ": Cannot read an undeclared parameter $param.", EntityException::READ_UNDECLARED);
		}

		// no any change
		if (!$this->originalParams) {
			return FALSE;
		}

		// fictive param is changed when is changed param which is dependent on
		// when dependencies are not loaded yet, param cannot be changed
		if (!$hasClear) {
			$depLoaded = FALSE;

			do {
				// search dependencies
				foreach ($this->dependencies as $onParam => $dependentParams){
					if (in_array($param, $dependentParams)) {
						if (array_key_exists($onParam, $this->originalParams)) {
							return TRUE;
						}
						$depLoaded = TRUE;
					}
				}

				// dependencies loaded, no change
				if ($depLoaded) {
					return FALSE;
				}

				// param loaded - dependencies loaded too, no change
				if (array_key_exists($param, $this->wrappedParams)) {
					return FALSE;
				}

				// load dependencies and search again
				$this->$param;
			}
			while (1);
		}

		// clear param
		return array_key_exists($param, $this->originalParams);
	}


	/**
	 * Sets param to NULL.
	 * @param string $param
	 * @throws EntityException when unset undeclared / private param
	 */
	public function __unset($param)
	{
		$this->$param = NULL;
	}


	/**
	 * Returns original value of param (it differs from current value when param is changed).
	 * @param string $param
	 * @return mixed
	 * @throws EntityException when read undeclared param
	 */
	final public function getOriginal($param)
	{
		$param[0] = strtolower($param[0]);
		$param = $this->translateParamName($param);

		if ($this->isChanged($param)) {
			return $this->originalParams[$param];
		}
		return array_key_exists($param, $this->params) ? $this->params[$param] : $this->getClear($param);
	}


	/**
	 * @param bool $throwFirst whether throw first IntegrityException from Checker
	 * @return void
	 */
	public function save($throwFirst = FALSE)
	{
		if ($this->isChanged()) {
			$this->accessor->save($this, $throwFirst);
			$this->bakeChanges();
		}
	}


	/**
	 * Resets changed param to original value including private and read-only params.
	 * @param string $param
	 * @throws EntityException on undeclared param
	 */
	final public function reset($param = NULL)
	{
		if (!is_null($param)) {
			try {
				if (!$this->isChanged($param)) {
					return;
				}
			}
			catch (EntityException $e){

				if (EntityException::READ_UNDECLARED === $e->getCode()) {
					throw new EntityException(get_class($this) . ": Cannot write to an undeclared parameter $param.'", EntityException::WRITE_UNDECLARED);
				} else {
					throw $e;
				}
			}

			$param[0] = strtolower($param[0]);
			$param = $this->translateParamName($param);

			$this->params[$param] = $this->originalParams[$param];
			unset($this->originalParams[$param]);
			$this->invalidDependent($param);
			return;
		}

		foreach ($this->originalParams as $param => $value) {
			$this->params[$param] = $value;
			$this->invalidDependent($param);
		}

		$this->originalParams = array();
	}


	/**
	 * Returns array of changed params with their clear values.
	 * @return array
	 */
	final public function getChanges()
	{
		return array_intersect_key($this->params, $this->originalParams);
	}


	/**
	 * @param string $entityClass
	 * @param string|IRestrictor $sourceParamOrRestrictor
	 * @param int|null $id id of Entity, when not accessible from source parameter
	 * @return IOperand
	 * @todo maybe when getter directly gets descendant and sourceParam is the same, sourceParam argument should be omitted?
	 */
	protected function getDescendant($entityClass, $sourceParamOrRestrictor, $id = NULL)
	{
		if ($sourceParamOrRestrictor instanceof IRestrictor) {
			return $this->accessor->getByRestrictions($entityClass, $sourceParamOrRestrictor, $this);
		} else {
			if (NULL === $id) {
				$id = $this->getClear($sourceParamOrRestrictor);
			}
			return $this->accessor->getById($entityClass, $id, $this, $sourceParamOrRestrictor);
		}
	}


	/**
	 * Bakes changed params, Entity is no longer changed.
	 * Call this method after successful saving.
	 */
	final protected function bakeChanges()
	{
		$this->originalParams = array();
	}


	/**
	 * Returns the clear variant of param.
	 * @param string $param
	 * @return mixed
	 * @throws EntityException
	 */
	protected function getClear($param)
	{
		$param[0] = strtolower($param[0]);
		$param = $this->translateParamName($param);

		$hasClear = $this->hasClear($param, $isLazy);

		// undeclared param
		if (!$hasClear) {
			throw new EntityException(get_class($this) . ": Cannot read an undeclared clear parameter $param'.", EntityException::READ_UNDECLARED);
		}

		if ($isLazy) {
			$this->params[$param] = $this->lazyLoad($param);
		}

		// dependencies on param
		if ($this->getting) {
			$this->writeDependency($param, end($this->getting));
		}

		return $this->params[$param];
	}


	/**
	 * Sets immutable param.
	 * @param string $param
	 * @param mixed $value
	 */
	protected function setReadOnlyOrPrivate($param, $value)
	{
		$this->setVar($param, $value, FALSE);
	}


	/**
	 * Says if param is private. Descendant can implement another solution.
	 * @param string $param
	 * @return bool
	 * @throws EntityException
	 */
	protected function isPrivate($param)
	{
		$param[0] = strtolower($param[0]);
		$param = $this->translateParamName($param);

		return in_array($param, $this->privateParams);
	}


	/**
	 * Method for extension prepared for lazy loading. When param does not exist, this can say
	 * it actually exists, but was not loaded by method load(). It will load by method lazyLoad().
	 * @param string $paramName
	 * @return bool
	 */
	protected function hasLazy($paramName)
	{
		return $this->accessor->hasParam($this, $paramName);
	}


	/**
	 * Lazy loading method for extension. When method hasLazy() said that param exists, this method
	 * loads it.
	 * @param string $paramName
	 * @return mixed
	 */
	protected function lazyLoad($paramName)
	{
		return $this->accessor->getParam($this, $paramName);
	}


	/**
	 * Translates input param. That means one param can have multiple representations.
	 * Extend this method to implement translation.
	 * For example if you put this code into method:
	 * <code>
	 *      return strtolower(preg_replace('~[A-Z]~', '_$0', $paramName));
	 * </code>
	 * it will be given underscores before capitals in param name, e.g. "serialNumber" becomes "serial_number".
	 * @param string $paramName
	 * @return string
	 */
	protected function translate($paramName)
	{
		return $paramName;
	}


	/**
	 * Says whether wrapper (method get{Param}) is available.
	 * @param string $param
	 * @param string|bool $wrapper name of method or FALSE when no method
	 * @return bool
	 */
	final protected function hasWrapper($param, &$wrapper = NULL)
	{
		$wrapper = FALSE;
		$param[0] = strtolower($param[0]);
		$param = $this->translateParamName($param);

		if (!isset($this->getIO()->wrappers[$param])) {
			return FALSE;
		}

		$wrapper = 'get'.ucfirst($param);
		return TRUE;
	}


	/**
	 * Says whether unwrapper (method set{Param}) is available.
	 * @param string $param
	 * @param string|bool $unwrapper name of method or FALSE when no method
	 * @return bool
	 */
	final protected function hasUnwrapper($param, &$unwrapper = NULL)
	{
		$unwrapper = FALSE;
		$param[0] = strtolower($param[0]);
		$param = $this->translateParamName($param);

		if (!isset($this->getIO()->unwrappers[$param])) {
			return FALSE;
		}

		$unwrapper = 'set'.ucfirst($param);
		return TRUE;
	}


	/********************* private methods *********************/


	private function hasClear($param, &$isLazy = NULL)
	{
		$isLazy = FALSE;
		if (array_key_exists($param, $this->params)) {
			return TRUE;
		}
		if ($this->hasLazy($param)) {
			return $isLazy = TRUE;
		}

		return FALSE;
	}


	/**
	 * Loads wrappers and unwrappers.
	 */
	private function getIO()
	{
		$class = get_class($this);

		if (!isset(self::$IO[$class])) {

			self::$IO[$class] = (object) array(
				'wrappers' => array(),
				'unwrappers' => array(),
			);

			foreach (get_class_methods($class) as $m) {
				if (0 === strpos($m, 'get')) {
					self::$IO[$class]->wrappers[strtolower($m[3]) . substr($m, 4)] = TRUE;

				} elseif (0 === strpos($m, 'set')) {
					self::$IO[$class]->unwrappers[strtolower($m[3]) . substr($m, 4)] = TRUE;
				}
			}
		}

		return self::$IO[$class];
	}


	private function setVar($param, $value, $checkImmutable)
	{
		$param[0] = strtolower($param[0]);
		$param = $this->translateParamName($param);

		$hasClear = $this->hasClear($param, $isLazy);
		$this->hasUnwrapper($param, $unwrapper);

		$exception = FALSE;

		if ($hasClear && !$unwrapper && $checkImmutable) {
			$exception = $this->isPrivate($param) ? EntityException::WRITE_UNDECLARED : EntityException::WRITE_READONLY;

		} elseif ($unwrapper && $this->isPrivate($param) && $checkImmutable) {
			$exception = EntityException::WRITE_UNDECLARED;

		} elseif (!$hasClear && !$unwrapper) {
			if (!$this->hasWrapper($param)) {
				$exception = EntityException::WRITE_UNDECLARED;

			} elseif ($this->isPrivate($param)) {
				$exception = EntityException::WRITE_UNDECLARED;

			} else {
				$exception = EntityException::WRITE_READONLY;
			}
		}

		if (FALSE !== $exception) {
			$kind = $exception === EntityException::WRITE_READONLY ? 'a read-only' : 'an undeclared';
			throw new EntityException(get_class($this)  . ": Cannot write to $kind parameter $param.", $exception);
		}

		// fictive param
		if (!$hasClear) {
			$this->$unwrapper($value);
			return;
		}

		// unwrap param
		if ($unwrapper) {
			try {
				$assigned = $value;
				$value = $this->$unwrapper($value);

				// possibly missing return in unwrapper
				if ($value === NULL && $assigned) {
					throw new Exception(
						"Unwrapper $unwrapper() has to return new value. "
					  . "If new value is NULL, throw NullValueException instead."
					);
				}
			}
			catch (NullValueException $e) {
				$value = NULL;
			}
		}

		// param changed first time
		if (!$this->isChanged($param)) {

			if ($isLazy) {
				$this->params[$param] = $this->lazyLoad($param);
			}

			// new value equals old one
			if ($value === $this->params[$param]) {
				return;
			}

			$this->originalParams[$param] = $this->params[$param];

		// new value equals original value, change is erased
		} elseif ($value === $this->originalParams[$param]) {
			unset($this->originalParams[$param]);
		}

		$this->params[$param] = $value;
		// invalid wrapped params dependent on current param
		$this->invalidDependent($param);
	}


	private function writeDependency($param, $dependentParam)
	{
		if ($param === $dependentParam) {
			return;
		}

		if (!isset($this->dependencies[$param])) {
			$this->dependencies[$param] = array();
		}
		$this->dependencies[$param][$dependentParam] = TRUE;
	}


	private function invalidDependent($onParam)
	{
		unset($this->wrappedParams[$onParam]);

		if (isset($this->dependencies[$onParam])) {
			foreach (array_keys($this->dependencies[$onParam]) as $dependentParam) {
				$this->invalidDependent($dependentParam);
			}
		}
	}


	private function translateParamName($paramName)
	{
		if (!$this->translate) {
			return $paramName;
		}

		// infinite loop protection
		static $last;
		if ($last === $paramName) {
			return $paramName;
		}
		$last = $paramName;

		if (!$this->accessor->hasParam($this, $paramName) && !$this->hasWrapper($paramName)) {
			return $this->translate($paramName);
		}
		return $paramName;
	}
}
