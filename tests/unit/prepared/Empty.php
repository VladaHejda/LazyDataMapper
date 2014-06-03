<?php

namespace LazyDataMapper\Tests {

	use LazyDataMapper\Entity;
	use LazyDataMapper\EntityCollection;

	class SomeFacade extends \LazyDataMapper\Facade
	{
		protected $entityClass = '';
	}

	class World extends Entity {}
	class Worlds extends EntityCollection {}
	class Story extends Entity {}
	class Stories extends EntityCollection {}
	class Some extends Entity {}
}

namespace LazyDataMapper\Tests\Some {

	class Facade extends \LazyDataMapper\Facade
	{
		protected $entityClass = '';
	}


	class ParamMap extends \LazyDataMapper\ParamMap
	{
		public function loadMap()
		{}
	}


	class Checker extends \LazyDataMapper\Checker
	{

		protected function checkCreate(\LazyDataMapper\IEntity $entity)
		{}

		protected function checkUpdate(\LazyDataMapper\IEntity $entity)
		{}
	}


	class Mapper extends \LazyDataMapper\Mapper
	{

		public function exists($id)
		{}

		public function getById($id, \LazyDataMapper\Suggestor $suggestor, \LazyDataMapper\DataHolder $dataHolder = NULL)
		{}


	}
}
