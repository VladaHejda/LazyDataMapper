<?php

namespace LazyDataMapper;

/**
 * @entityDependent
 * @todo name it Collection?
 */
interface IEntityCollection extends IOperand, \ArrayAccess, \Iterator, \Countable
{
}
