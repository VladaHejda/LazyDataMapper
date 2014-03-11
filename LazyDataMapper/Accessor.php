<?php

namespace LazyDataMapper;

/**
 * The main class leading all dependencies.
 * @todo baseNamespace setting? It could be for example AppName\Entities - will be added during new instance creating, but not stored to cache
 */
final class Accessor
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


	/********************* interface for Facade *********************/


	/**
	 * Do not call this method directly!
	 * @param array|string $entityClass
	 * @param int $id
	 * @param IOperand $parent
	 * @param string $sourceParam
	 * @return IEntity
	 * @throws Exception
	 */
	public function getById($entityClass, $id, IOperand $parent = NULL, $sourceParam = NULL)
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
	 * @param IRestrictor|int[] $restrictions
	 * @param IOperand $parent
	 * @param int $maxCount
	 * @return IEntityContainer
	 * @throws Exception on wrong restrictions
	 */
	public function getByRestrictions($entityClass, $restrictions, IOperand $parent = NULL, $maxCount = NULL)
	{
		list($entityClass, $entityContainerClass) = $this->extractEntityClasses($entityClass);

		$ids = $this->loadIdsByRestrictions($entityClass, $restrictions, $maxCount);

		$identifier = $this->serviceAccessor->composeIdentifier($entityClass, TRUE, $parent ? $parent->getIdentifier() : NULL);

		if (!empty($ids)) {
			$suggestor = $this->cache->getCached($identifier, $entityClass);
		}

		if (empty($ids)) {
			$data = array();

		} elseif (!$suggestor) {
			// nonexistent ids prevention
			foreach ($ids as $i => $id) {
				if (!$this->serviceAccessor->getMapper($entityClass)->exists($id)) {
					unset($ids[$i]);
				}
			}
			$data = array_fill_keys($ids, array());

		} else {
			$dataHolder = $this->loadDataHolderByMapper($entityClass, $ids, $suggestor);
			$this->saveDescendants($dataHolder);
			$data = $dataHolder->getParams();
			$this->sortData($ids, $data);
		}

		return $this->createEntityContainer($entityContainerClass, $data, $identifier, $entityClass);
	}


	/**
	 * @param array|string $entityClass
	 * @param array $publicData
	 * @param array $privateData
	 * @param bool $throwFirst
	 * @return IEntity
	 * @throws Exception
	 */
	public function create($entityClass, array $publicData, array $privateData = array(), $throwFirst = TRUE)
	{
		list($entityClass) = $this->extractEntityClasses($entityClass);

		$entity = $this->createEntity($entityClass, NULL, $privateData);

		$cachedException = NULL;
		foreach ($publicData as $paramName => $value) {
			try {
				$entity->$paramName = $value;
			} catch (IntegrityException $e) {
				if ($throwFirst) {
					throw $e;
				}
				if (!$cachedException) {
					$cachedException = $e;
				} else {
					$cachedException->addMessage($e->getMessage(), $paramName);
				}
			}
		}

		if ($cachedException) {
			throw $cachedException;
		}

		if ($checker = $this->getChecker($entityClass)) {
			$checker->check($entity, TRUE, $throwFirst);
		}

		$data = $entity->getChanges() + $privateData;
		$dataHolder = $this->createDataHolder($entityClass, array_keys($data));
		$dataHolder->setParams($data);

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
	 * @param array|string $entityClass
	 * @param int $id
	 */
	public function remove($entityClass, $id)
	{
		list($entityClass) = $this->extractEntityClasses($entityClass);
		$this->serviceAccessor->getMapper($entityClass)->remove($id);
	}


	/**
	 * @param string $entityClass
	 * @param IRestrictor|int[] $restrictions
	 * @throws Exception
	 */
	public function removeByRestrictions($entityClass, $restrictions)
	{
		list($entityClass) = $this->extractEntityClasses($entityClass);

		$ids = $this->loadIdsByRestrictions($entityClass, $restrictions);

		if (!empty($ids)) {
			$this->serviceAccessor->getMapper($entityClass)->removeByIdsRange($ids);
		}
	}


	/********************* interface for IEntity *********************/


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
		$params = $dataHolder->getParams();
		return array_shift($params);
	}


	/**
	 * @param IEntity $entity
	 * @param string $paramName
	 * @return string
	 */
	public function getDefaultParam(IEntity $entity, $paramName)
	{
		$entityClass = get_class($entity);
		return $this->serviceAccessor->getParamMap($entityClass)->getDefaultValue($paramName);
	}


	/**
	 * @param IEntity $entity
	 * @param bool $throwFirst
	 */
	public function save(IEntity $entity, $throwFirst = TRUE)
	{
		$entityClass = get_class($entity);
		if ($checker = $this->getChecker($entityClass)) {
			$checker->check($entity, FALSE, $throwFirst);
		}

		$data = $entity->getChanges();
		$dataHolder = $this->createDataHolder($entityClass, array_keys($data));
		$dataHolder->setParams($data);

		$this->serviceAccessor->getMapper($entityClass)->save($entity->getId(), $dataHolder);
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
	protected function createEntity($entityClass, $id, array $data, IIdentifier $identifier = NULL)
	{
		return new $entityClass($id, $data, $this, $identifier);
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
	 * @param IRestrictor|int[] $restrictions
	 * @param int $maxCount
	 * @return int[]
	 * @throws Exception
	 * @throws TooManyItemsException
	 */
	private function loadIdsByRestrictions($entityClass, $restrictions, $maxCount = NULL)
	{
		if (is_array($restrictions)) {
			return $restrictions;
		}

		if (!$restrictions instanceof IRestrictor) {
			throw new Exception('Expected instance of IRestrictor or an array.');
		}

		$mapper = $this->serviceAccessor->getMapper($entityClass);
		$ids = $mapper->getIdsByRestrictions($restrictions, NULL === $maxCount ? NULL : ++$maxCount);
		if (NULL === $ids) {
			return array();

		} elseif (!is_array($ids)) {
			throw new Exception(get_class($mapper) . '::getIdsByRestrictions() must return array or null.');
		}

		if (NULL !== $maxCount && count($ids) > $maxCount) {
			throw new TooManyItemsException("Trying to get more than $maxCount pieces of $entityClass. Increase the \$maxCount limit or restrict result more.");
		}

		return $ids;
	}


	/**
	 * @param string $entityClass
	 * @param int|int[] $id or ids
	 * @param ISuggestor $suggestor
	 * @param int $maxCount
	 * @return IDataHolder
	 * @throws Exception
	 */
	private function loadDataHolderByMapper($entityClass, $id, ISuggestor $suggestor, $maxCount = NULL)
	{
		$isContainer = is_array($id);
		$mapper = $this->serviceAccessor->getMapper($entityClass);
		if ($isContainer) {
			$m = 'getByIdsRange';
			$datHolder = new DataHolder($suggestor, $id);
			$dataHolder = $mapper->getByIdsRange($id, $suggestor, $datHolder, $maxCount);
		} else {
			$m = 'getById';
			$datHolder = new DataHolder($suggestor);
			$dataHolder = $mapper->getById($id, $suggestor, $datHolder);
		}

		if (!$dataHolder instanceof IDataHolder) {
			throw new Exception(get_class($mapper) . "::$m() must return loaded IDataHolder instance.");
		}
		if ($isContainer && !$dataHolder->isContainer()) {
			throw new Exception(get_class($mapper) . "::$m() you forgot to add second argument into IDataHolder (ids array).");
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
			if (isset($data[$id])) {
				$sorted[$id] = $data[$id];
			}
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
