<?php

namespace Shelter;

/**
 * Outer cover for getting operands (Entity or EntityContainer).
 * Primary name entity-dependent descendant of this class like <Entity>Facade. If you
 * want use another naming convention, override method getEntityClass.
 */
abstract class Facade implements IFacade
{

	/**
	 * @param IAccessor $accessor
	 */
	public function __construct(IAccessor $accessor)
	{
	}


	/**
	 * @param int $id
	 * @return IEntity
	 */
	public function getById($id)
	{
	}


	/**
	 * @param IRestrictor $restrictor
	 * @return IEntityContainer
	 */
	public function getByRestrictions(IRestrictor $restrictor)
	{
	}


	/**
	 * Apply mandatory arguments for new Entity.
	 * @todo how to implement? - array $data is one way, but method can be overridden in descendant and given as array to parent::create()
	 * @param array $data
	 * @return IEntity
	 */
	public function create(array $data = array())
	{
	}


	/**
	 * Returns Entity classname.
	 * @return string
	 */
	protected function getEntityClass()
	{
	}
}
