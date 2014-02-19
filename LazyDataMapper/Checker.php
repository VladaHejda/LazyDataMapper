<?php

namespace LazyDataMapper;

/**
 * @todo !pokud checker dostane DataHolder, nemůže v něm modifikovat data
 *       do creatu se data vkládají wrapnutá, v checkeru je očekáváš wrapnutá (stejně jako od Entity), ale v mapperu už je očekáváš clear (unwrapnutá)
 *          což ale DataHolder nesplňuje!
 *       vše nasvědčuje tomu, založit Entitu ještě dřív, než bude skutečně existovat - čili měla by dva mody:
 *          1. normální mod ve kterym funguje teď
 *          2. mod kdy nemá přiděleno id a neznámý parametry získají hodnotu NULL, jenže u těch vzniká problém, že skutečně přidělený hodnoty po
 *              vytvoření rozhodně NULL být nemusí. Řešením by mohl být seznam defaultních hodnot - ale to duplikuje informaci s datovým úložištěm
 *              a muselo by se spravovat na dvou místech.
 */
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
	 * @param IDataHolder $dataHolder
	 * @return void
	 * @throws IntegrityException
	 */
	abstract protected function checkCreate(IDataHolder $dataHolder);


	/**
	 * @param IDataEnvelope $subject
	 * @param bool $throwFirst
	 * @throws Exception
	 */
	final public function check(IDataEnvelope $subject, $throwFirst = FALSE)
	{
		$this->stack[] = array($subject, $throwFirst);

		if ($subject instanceof IEntity) {
			$this->checkUpdate($subject);

		} elseif ($subject instanceof IDataHolder) {
			$this->checkCreate($subject);

		} else {
			throw new Exception(get_class($this) . ": expected IEntity or IDataHolder, got " . (is_object($subject) ? get_class($subject) : gettype($subject)) . '.');
		}

		array_pop($this->stack);
		$this->throwException();
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
}
