<?php

namespace Shelter;

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
}

/**
 * When entity unwrapper (setter) need to return NULL, throws this exception.
 * It's designed to protect saving NULL after forgotten return statement.
 */
class NullValueException extends \Exception
{
}

/**
 * Exception that gathers several messages when it's suitable to provide more error messages together.
 */
class MultiException extends Exception
{

	/** @var string[] */
	protected $messages = array();


	public function __construct($message = '', $code = 0, \Exception $previous = null)
	{
		if ($message) $this->messages[] = $message;
		parent::__construct($message, $code, $previous);
	}


	public function addMessage($message = '')
	{
		if (!$this->message) $this->message = $message;
		$this->messages[] = $message;
	}


	public function getAllMessages()
	{
		return $this->messages;
	}
}

/**
 * Thrown when integrity of Entity's modified parameters fails.
 */
class IntegrityException extends MultiException
{
}

/**
 * todo viz IMapper - anotaci maxCount jsem zamejšlel jak??
 * Thrown when Restrictor limits are too weak and result is too long.
 */
class TooManyItemsException extends \Exception
{
}
