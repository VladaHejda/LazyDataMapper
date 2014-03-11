<?php

namespace LazyDataMapper;

/**
 * Common model Exception.
 */
class Exception extends \Exception
{
}


/**
 * Exception thrown by Entity / EntityContainer.
 */
class EntityException extends \Exception
{
	/** exception codes */
	const READ_UNDECLARED = 10,
		WRITE_UNDECLARED = 20,
		WRITE_READONLY = 30;
}


/**
 * When entity unwrapper (setter) need to return NULL, throws this exception.
 * It's designed to protect saving NULL after forgotten return statement.
 */
class NullValueException extends \Exception
{
}


/**
 * Thrown when integrity of Entity's modified parameters fails.
 * Exception gathers several messages when it's suitable to provide more error messages together.
 */
class IntegrityException extends Exception
{

	/** @var string[] */
	protected $messages = array();


	public function __construct($message = '', $paramName = NULL, $code = 0, \Exception $previous = null)
	{
		if (NULL !== $message) {
			if (NULL === $paramName) {
				$this->messages[] = $message;
			} else {
				$this->messages[$paramName] = $message;
			}
		}
		parent::__construct($message, $code, $previous);
	}


	/**
	 * Sets the parameter name for first error message.
	 */
	public function setParamName($paramName)
	{
		if (!$this->messages) {
			throw new Exception("There's no message to assign parameter.");
		}

		$message = array_shift($this->messages);
		$this->messages = array_merge(array($paramName => $message), $this->messages);
	}


	public function addMessage($message = '', $paramName = NULL)
	{
		if (NULL === $this->message) {
			$this->message = $message;
		}
		if (NULL === $paramName) {
			$this->messages[] = $message;
		} else {
			$this->messages[$paramName] = $message;
		}
	}


	public function getAllMessages()
	{
		return $this->messages;
	}
}


/**
 * Thrown when Restrictor limits are too weak and result is too long.
 */
class TooManyItemsException extends \Exception
{
}
