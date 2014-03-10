<?php

namespace LazyDataMapper;

abstract class Checker implements IChecker
{

	/** @var IntegrityException */
	private $exception;

	/** @var array option stack */
	private $stack = array();


	/**
	 * @param IEntity $entity
	 * @return void
	 * @throws IntegrityException
	 */
	abstract protected function checkUpdate(IEntity $entity);


	/**
	 * @param IEntity $entity
	 * @return void
	 * @throws IntegrityException
	 */
	abstract protected function checkCreate(IEntity $entity);


	/**
	 * @param IEntity $entity
	 * @param bool $creation
	 * @param bool $throwFirst
	 * @throws Exception
	 */
	final public function check(IEntity $entity, $creation = FALSE, $throwFirst = FALSE)
	{
		$this->stack[] = array($entity, $throwFirst);

		if ($creation) {
			$this->checkCreate($entity);
		} else {
			$this->checkUpdate($entity);
		}

		array_pop($this->stack);
		$this->throwException();
	}


	/**
	 * If exception occurred, throws it.
	 */
	final public function throwException()
	{
		if ($this->exception) {
			$e = $this->exception;
			$this->exception = NULL;
			throw $e;
		}
	}


	/**
	 * @param array $paramNames
	 */
	final protected function checkRequired(array $paramNames)
	{
		list($subject) = end($this->stack);

		foreach ($paramNames as $paramName) {
			if (!$subject->$paramName) {
				$class = $subject instanceof IEntity ? get_class($subject) : get_class($this);
				$this->addError($class . ": required parameter $paramName is not set.");
			}
		}
	}


	/**
	 * @param string $name
	 * @throws Exception
	 */
	final protected function addCheck($name)
	{
		list($subject) = end($this->stack);

		if (in_array($name, array('update', 'create', 'required'))) {
			throw new Exception(get_class($this) . ": '$name' is a reserved word and cannot be used for check method.");
		}

		$name[0] = strtoupper($name[0]);
		$m = "check$name";

		if (!method_exists($this, $m)) {
			throw new Exception(get_class($this) . ": checking method $m() does not exist.");
		}

		call_user_func(array($this, $m), $subject);
	}


	/**
	 * Adds new error message into cached exception.
	 */
	final protected function addError($message)
	{
		list(, $throwFirst) = end($this->stack);

		if (!$this->exception) {
			$this->exception = new IntegrityException($message);

		} else {
			$this->exception->addMessage($message);
		}

		if ($throwFirst) {
			$this->throwException();
		}
	}
}
