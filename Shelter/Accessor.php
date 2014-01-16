<?php

namespace Shelter;

class Accessor implements IAccessor
{

	/** @var ISuggestorCache */
	protected $cache;

	/** @var IEntityServiceAccessor */
	protected $serviceAccessor;

	/** @var array */
	private $loadedData = array();


	/**
	 * @param ISuggestorCache $cache
	 * @param IEntityServiceAccessor $serviceAccessor
	 */
	public function __construct(ISuggestorCache $cache, IEntityServiceAccessor $serviceAccessor)
	{
		$this->cache = $cache;
		$this->serviceAccessor = $serviceAccessor;
	}


	/**
	 * Do not call this method directly!
	 * @param int $id
	 * @param string $entityClass
	 * @param IOperand $parent
	 * @param string $sourceParam
	 * @return IEntity
	 */
	public function getById($entityClass, $id, IOperand $parent = NULL, $sourceParam = NULL)
	{
		$identifier = $this->composeIdentifier($entityClass, FALSE, $parent ? $parent->getIdentifier() : NULL, $sourceParam);

		if (NULL !== $parent && $loadedData = $this->getLoadedData($parent->getIdentifier(), $entityClass, $sourceParam)) {
			$data = $loadedData;

		} else {
			$mapper = $this->serviceAccessor->getMapper($entityClass);
			if (!$mapper->exists($id)) {
				return NULL;
			}

			if ($suggestor = $this->cache->getCached($identifier, $entityClass)) {
				$dataHolder = $this->loadDataHolderByMapper($entityClass, $id, $suggestor);
				$this->saveDescendants($dataHolder);
				$data = $dataHolder->getParams();

			} else {
				// cachování descendanty by možná mohlo bejt třeba i v případě že paramNamy už byly zakešovány - descendant mohl bejt nějakej podmíneněj? je to tak???
				// todo rewrite if (NULL !== $object) to just if ($object)
				if (NULL !== $parent) {
					$this->cache->cacheDescendant($parent->getIdentifier(), $entityClass, $sourceParam);
				}
				$data = array();
			}
		}

		return $this->createEntity($entityClass, $id, $data, $identifier, $parent);
	}


	/**
	 * Do not call this method directly!
	 * @param IRestrictor $restrictor
	 * @param string $entityClass
	 * @param IOperand $parent
	 * @return IEntityContainer
	 */
	public function getByRestrictions($entityClass, IRestrictor $restrictor, IOperand $parent = NULL)
	{
		$identifier = $this->composeIdentifier($entityClass, TRUE, $parent ? $parent->getIdentifier() : NULL);
		$mapper = $this->serviceAccessor->getMapper($entityClass);
		$ids = $mapper->getIdsByRestrictions($restrictor);

		if (!empty($ids)) {
			$suggestor = $this->cache->getCached($identifier, $entityClass);
		}

		if (empty($ids) || !$suggestor) {
			$data = array();

		} else {
			$dataHolder = $this->loadDataHolderByMapper($entityClass, $ids, $suggestor);
			$this->saveDescendants($dataHolder);
			$data = $dataHolder->getParams();
			$this->sortData($ids, $data);
		}

		return $this->createEntityContainer($entityClass, $data, $identifier, $parent);
	}


	/**
	 * @param IEntity $entity
	 * @param string $paramName
	 * @return string
	 */
	public function getParam(IEntity $entity, $paramName)
	{
		$entityClass = get_class($entity);
		$suggestor = $this->cache->cacheParamName($entity->getIdentifier(), $paramName, $entityClass);
		$dataHolder = $this->serviceAccessor->getMapper($entityClass)->getById($entity->getId(), $suggestor);
		return array_shift($dataHolder->getParams());
	}


	/**
	 * @param string $entityClass
	 * @param array $data
	 * @param bool $check
	 * @return IEntity
	 */
	public function create($entityClass, array $data, $check = TRUE)
	{
		$dataHolder = $this->createDataHolder($entityClass, array_keys($data));
		$dataHolder->setParams($data);
		if ($check) {
			$this->check($entityClass, NULL, $dataHolder);
		}

		$id = $this->serviceAccessor->getMapper($entityClass)->create($dataHolder);
		$identifier = $this->composeIdentifier($entityClass, FALSE);

		if ($suggestorCached = $this->cache->getCached($identifier, $entityClass)) {
			$data += $this->serviceAccessor->getMapper($entityClass)->getById($id, $suggestorCached)->getParams();
		}

		return $this->createEntity($entityClass, $id, $data, $identifier);
	}


	/**
	 * @param IEntity $entity
	 */
	public function save(IEntity $entity)
	{
		$entityClass = get_class($entity);
		$data = $entity->getChanges();
		$dataHolder = $this->createDataHolder($entityClass, array_keys($data));
		$dataHolder->setParams($data);
		$this->check($entityClass, $entity, $dataHolder);
		$this->serviceAccessor->getMapper($entityClass)->save($entity->getId(), $dataHolder);
	}


	/**
	 * @param string $entityClass
	 * @param int $id
	 */
	public function remove($entityClass, $id)
	{
		$this->serviceAccessor->getMapper($entityClass)->remove($id);
	}


	/********************* factories *********************/


	/**
	 * @param string $entityClass
	 * @param bool $isContainer todo now is unnecessary
	 * @param string $parentIdentifier
	 * @param string $sourceParam
	 * @return string
	 */
	protected function composeIdentifier($entityClass, $isContainer, $parentIdentifier = NULL, $sourceParam = NULL)
	{
		$identifier = new Identifier($entityClass, $isContainer, $parentIdentifier, $sourceParam);
		return $identifier->composeIdentifier();
	}


	/**
	 * @param string $entityClass
	 * @param int $id
	 * @param array $data
	 * @param string $identifier
	 * @param IOperand $parent
	 * @return IEntity
	 */
	protected function createEntity($entityClass, $id, array $data, $identifier, IOperand $parent = NULL)
	{
		return new $entityClass($id, $data, $identifier, $parent, $this);
	}


	/**
	 * @param string $entityClass
	 * @param array[] $data
	 * @param string $identifier
	 * @param IOperand $parent
	 * @return IEntityContainer
	 */
	protected function createEntityContainer($entityClass, array $data, $identifier, IOperand $parent = NULL)
	{
		$containerClass = $this->serviceAccessor->getEntityContainerClass($entityClass);
		return new $containerClass($data, $identifier, $parent, $this);
	}


	/**
	 * @param string $entityClass
	 * @param array $paramNames
	 * @return DataHolder
	 */
	protected function createDataHolder($entityClass, array $paramNames)
	{
		$suggestor = new Suggestor($this->serviceAccessor->getParamMap($entityClass), $this->cache, $paramNames);
		return new DataHolder($suggestor);
	}


	/********************* internal *********************/


	/**
	 * @param string $entityClass
	 * @param int|int[] $id
	 * @param ISuggestor $suggestor
	 * @return IDataHolder
	 * @throws Exception
	 */
	private function loadDataHolderByMapper($entityClass, $id, ISuggestor $suggestor)
	{
		$m = is_array($id) ? 'getByIdsRange' : 'getById';
		$dataHolder = $this->serviceAccessor->getMapper($entityClass)->$m($id, $suggestor);

		if (!$dataHolder instanceof IDataHolder) {
			throw new Exception("Method $m() of mapper for $entityClass must return IDataHolder instance.");
		}
		return $dataHolder;
	}


	private function getLoadedData($parentIdentifier, $entityClass, $sourceParam)
	{
		if (isset($this->loadedData[$parentIdentifier][$entityClass])) {
			$loaded = $this->loadedData[$parentIdentifier][$entityClass];
			if (is_array($loaded)) {
				if (isset($loaded[$sourceParam])) {
					return $loaded[$sourceParam];
				}
			} else {
				return $loaded[$entityClass];
			}
		}
		return FALSE;
	}


	// todo zde by nemusel nustně dostat sourceParam - holder ho zná. pokud opravim, opravit testy
	// todo cachovat i data aktuálního? pak by se to teda nejmenoval saveDescendants ale saveData. a bylo by to k něčemu?
	//      a cachovat paramy nebo celej holder? k čemu by byl holder?
	private function saveDescendants(IDataHolder $dataHolder)
	{
		/** @var IDataHolder $descendant */
		foreach ($dataHolder as $descendant) {

			$identifier = $descendant->getSuggestor()->getIdentifier();

			// tyto informace (isContainer, getSourceParam) by měl vědět už suggestor, do toho se dostanou keškou. nebo jsem to měl vymyšlený jinak?
			// když se podívám do SuggestorCache tak metoda cacheDescendant skutečně přebírá sourceParam, takže ho zakešuje a když si pak Accessor
			// řekne o getCached, SuggestorCache mu musí vrátit i informaci o sourceParam, jenže tu suggestor o sobě nenabízí...
			// ale všude koukám se pravděpodobně počítá s tím, že container nemá sourceParam.
			// asi teda zatim vyrobim původní koncept - že container nemůže mít sourceParam (!upravit IdentifierTest), předělat pozdějc to kdyžtak pude
			// todo a v tom případě ani Identifier nepotřebuje mít informaci zda jde o container... ale neni to celý moc clear.. :-X

			if ($descendant->getSuggestor()->hasDescendants()) {
				$this->saveDescendants($descendant);
			}

			$this->loadedData[$identifier] = $descendant->getParams();
		}
	}


	private function sortData(array $ids, array $data)
	{
		$sorted = array();
		foreach ($ids as $id) {
			$sorted[$id] = isset($data[$id]) ? $data[$id] : array();
		}
		return $sorted;
	}


	private function check($entityClass, $entity, IDataHolder $dataHolder)
	{
		$checker = $this->serviceAccessor->getChecker($entityClass);
		if ($checker) {
			$checker->check($entity, $dataHolder);
		}
	}
}
