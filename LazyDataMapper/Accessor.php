<?php

namespace LazyDataMapper;

/**
 * @todo make some methods final (this is pivotal class of model)
 */
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
	 * @param array|string $entityClass
	 * @param int $id
	 * @param IOperand $parent
	 * @param string $sourceParam
	 * @return IEntity
	 * @throws Exception
	 */
	final public function getById($entityClass, $id, IOperand $parent = NULL, $sourceParam = NULL)
	{
		list($entityClass) = $this->extractEntityClasses($entityClass);

		if (($parent && NULL === $sourceParam) || (NULL !== $sourceParam && !$parent)) {
			throw new Exception('Both $parent and $sourceParam must be set or omitted.');
		}

		$identifier = $this->serviceAccessor->composeIdentifier($entityClass, FALSE, $parent ? $parent->getIdentifier() : NULL, $sourceParam);

		if ($parent && $data = $this->getLoadedData($identifier)) {
			// $data set

		} else {
			if (!$this->serviceAccessor->getMapper($entityClass)->exists($id)) {
				return NULL;
			}

			if ($suggestor = $this->cache->getCached($identifier, $entityClass)) {
				$dataHolder = $this->loadDataHolderByMapper($entityClass, $id, $suggestor);
				$this->saveDescendants($dataHolder);
				$data = $dataHolder->getParams();

			} else {
				if ($parent) {
					$this->cache->cacheDescendant($parent->getIdentifier(), $entityClass, $sourceParam);
				}
				$data = array();
			}
		}

		return $this->createEntity($entityClass, $id, $data, $identifier);
	}


	/**
	 * Do not call this method directly!
	 * @param array|string $entityClass
	 * @param IRestrictor|int[] $restrictor
	 * @param IOperand $parent
	 * @return IEntityContainer
	 * @throws Exception on wrong restrictions
	 * @todo pokud dostane pole s idčkama, neproběhne kontrola jejich existence (což předtim nemuseal proběhnout taky)
	 *       případnej container pak může místo entity vrátit NULL, což by neměl. Kontrolovat existenci idéček?
	 */
	public function getByRestrictions($entityClass, $restrictor, IOperand $parent = NULL)
	{
		if (!$restrictor instanceof IRestrictor && !is_array($restrictor)) {
			throw new Exception('Expected instance of IRestrictor or an array.');
		}

		list($entityClass, $entityContainerClass) = $this->extractEntityClasses($entityClass);

		$identifier = $this->serviceAccessor->composeIdentifier($entityClass, TRUE, $parent ? $parent->getIdentifier() : NULL);

		if (is_array($restrictor)) {
			$ids = $restrictor;
		} else {
			$mapper = $this->serviceAccessor->getMapper($entityClass);
			$ids = $mapper->getIdsByRestrictions($restrictor);
			if (NULL === $ids) {
				$ids = array();
			} elseif (!is_array($ids)) {
				throw new Exception(get_class($mapper) . '::getIdsByRestrictions() must return array or null.');
			}
		}

		if (!empty($ids)) {
			$suggestor = $this->cache->getCached($identifier, $entityClass);
		}

		if (empty($ids) || !$suggestor) {
			$data = array_fill_keys($ids, array());

		} else {
			$dataHolder = $this->loadDataHolderByMapper($entityClass, $ids, $suggestor);
			$this->saveDescendants($dataHolder);
			$data = $dataHolder->getParams();
			$this->sortData($ids, $data);
		}

		return $this->createEntityContainer($entityContainerClass, $data, $identifier, $entityClass);
	}


	public function hasParam(IEntity $entity, $paramName)
	{
		$entityClass = get_class($entity);
		return $this->serviceAccessor->getParamMap($entityClass)->hasParam($paramName);
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
		$dataHolder = $this->loadDataHolderByMapper($entityClass, $entity->getId(), $suggestor);
		return array_shift($dataHolder->getParams());
	}


	/**
	 * @param array|string $entityClass
	 * @param array $data
	 * @param bool $throwFirst
	 * @return IEntity
	 * @throws Exception
	 */
	public function create($entityClass, array $data, $throwFirst = TRUE)
	{
		list($entityClass) = $this->extractEntityClasses($entityClass);

		$dataHolder = $this->createDataHolder($entityClass, array_keys($data));
		$dataHolder->setParams($data);
		if ($checker = $this->getChecker($entityClass)) {
			$checker->check($dataHolder, $throwFirst);
		}

		$mapper = $this->serviceAccessor->getMapper($entityClass);
		$id = $mapper->create($dataHolder);
		if (!is_int($id)) {
			throw new Exception(get_class($mapper) . '::create() must return integer id.');
		}
		$identifier = $this->serviceAccessor->composeIdentifier($entityClass);

		if ($suggestorCached = $this->cache->getCached($identifier, $entityClass)) {
			// todo if there is a conflict in input data and cached data, it is not necessary to get these data from cache
			//      so then should be this data removed form suggestor, but how? - create new suggestor or add method to set/remove suggestions ?
			$data += $this->loadDataHolderByMapper($entityClass, $id, $suggestorCached)->getParams();
		}

		return $this->createEntity($entityClass, $id, $data, $identifier);
	}


	/**
	 * @param IEntity $entity
	 * @param bool $throwFirst
	 */
	public function save(IEntity $entity, $throwFirst = FALSE)
	{
		$entityClass = get_class($entity);
		if ($checker = $this->getChecker($entityClass)) {
			$checker->check($entity, $throwFirst);
		}

		$data = $entity->getChanges();
		$dataHolder = $this->createDataHolder($entityClass, array_keys($data));
		$dataHolder->setParams($data);

		$this->serviceAccessor->getMapper($entityClass)->save($entity->getId(), $dataHolder);
	}


	/**
	 * @param array|string $entityClass
	 * @param int $id
	 */
	public function remove($entityClass, $id)
	{
		list($entityClass) = $this->extractEntityClasses($entityClass);
		$this->serviceAccessor->getMapper($entityClass)->remove($id);
	}


	/********************* factories *********************/


	/**
	 * @param string $entityClass
	 * @param int $id
	 * @param array $data
	 * @param IIdentifier $identifier
	 * @return IEntity
	 * @todo conditional Entities (e.g. unit with vendor HTC creates Entity HtcUnit, but with same identifier as other)
	 */
	protected function createEntity($entityClass, $id, array $data, IIdentifier $identifier)
	{
		return new $entityClass($id, $data, $identifier, $this);
	}


	/**
	 * @param string $containerClass
	 * @param array[] $data
	 * @param IIdentifier $identifier
	 * @param string $entityClass
	 * @return IEntityContainer
	 */
	protected function createEntityContainer($containerClass, array $data, IIdentifier $identifier, $entityClass)
	{
		return new $containerClass($data, $identifier, $this, $entityClass);
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
	 * @param int|int[] $id or ids
	 * @param ISuggestor $suggestor
	 * @return IDataHolder
	 * @throws Exception
	 */
	private function loadDataHolderByMapper($entityClass, $id, ISuggestor $suggestor)
	{
		$m = is_array($id) ? 'getByIdsRange' : 'getById';
		$mapper = $this->serviceAccessor->getMapper($entityClass);
		$dataHolder = $mapper->$m($id, $suggestor);

		if (!$dataHolder instanceof IDataHolder) {
			throw new Exception(get_class($mapper) . "::$m() must return IDataHolder instance.");
		}
		return $dataHolder;
	}


	private function getLoadedData(IIdentifier $identifier)
	{
		$key = $identifier->getKey();
		return isset($this->loadedData[$key]) ? $this->loadedData[$key] : FALSE;
	}


	// todo cachovat i data aktuálního? pak by se to teda nejmenoval saveDescendants ale saveData. a bylo by to k něčemu?
	private function saveDescendants(IDataHolder $dataHolder)
	{
		/** @var IDataHolder $descendant */
		foreach ($dataHolder as $descendant) {
			if ($descendant->getSuggestor()->hasDescendants()) {
				$this->saveDescendants($descendant);
			}

			$identifier = $descendant->getSuggestor()->getIdentifier();
			$this->loadedData[$identifier->getKey()] = $descendant->getParams();
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


	/**
	 * @param string $entityClass
	 * @return IChecker
	 */
	private function getChecker($entityClass)
	{
		$checker = $this->serviceAccessor->getChecker($entityClass);
		if ($checker instanceof IChecker) {
			return $checker;
		}
		return NULL;
	}


	/**
	 * @param array|string $entityClass
	 * @return array
	 */
	private function extractEntityClasses($entityClass)
	{
		return is_array($entityClass) ? $entityClass : array($entityClass, $this->serviceAccessor->getEntityContainerClass($entityClass));
	}
}
