<?php

namespace LazyDataMapper;

/**
 * The main class leading all dependencies. Everything there is controlled internally.
 * @todo baseNamespace setting? It could be for example AppName\Entities - will be added during new instance creating, but not stored to cache
 */
final class Accessor
{

	/** @var SuggestorCache */
	protected $cache;

	/** @var IEntityServiceAccessor */
	protected $serviceAccessor;

	/** @var array */
	private $loadedData = array();

	/** @var array */
	private $childrenIdentifierList = array();


	/**
	 * @param SuggestorCache $cache
	 * @param IEntityServiceAccessor $serviceAccessor
	 */
	public function __construct(SuggestorCache $cache, IEntityServiceAccessor $serviceAccessor)
	{
		$this->cache = $cache;
		$this->serviceAccessor = $serviceAccessor;
	}


	/********************* interface for Facade *********************/


	/**
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

		$parentIdentifier = $parent ? $parent->getIdentifier() : NULL;
		$identifier = $this->serviceAccessor->composeIdentifier($entityClass, FALSE, $parentIdentifier, $sourceParam);

		if ($parent) {
			$hierarchy = $parent->getHierarchy()->appendAndCreate($id, $identifier);
			$data = $this->getLoadedData($hierarchy);
		} else {
			$hierarchy = Hierarchy::create($id, $identifier);
		}

		if (!isset($data) || FALSE === $data) {
			if (!$this->serviceAccessor->getMapper($entityClass)->exists($id)) {
				return NULL;
			}

			if ($suggestor = $this->cache->getCached($identifier, $entityClass, $hierarchy, $childrenIdentifierList)) {
				// when no suggestions, even children are ignored, they will be loaded later
				$suggestions = $suggestor->getSuggestions();
				if (empty($suggestions)) {
					$data = array();

				} else {
					$dataHolder = $this->loadDataHolderByMapper($entityClass, $id, $suggestor);
					if ($dataHolder->hasLoadedChildren()) {
						$this->saveChildren($dataHolder);
					}
					$data = $dataHolder->getParams();
				}

			} else {
				if ($parent && !isset($this->childrenIdentifierList[$identifier->getKey()])) {
					$this->cache->cacheChild($parent->getIdentifier(), $entityClass, $sourceParam);
				}
				$data = array();
			}

			unset($this->childrenIdentifierList[$identifier->getKey()]);

			if (!empty($childrenIdentifierList)) {
				$this->childrenIdentifierList += array_fill_keys($childrenIdentifierList, TRUE);
			}
		}

		return $this->createEntity($entityClass, $id, $data, $hierarchy);
	}


	/**
	 * @param array|string $entityClass
	 * @param IRestrictor|int[] $restrictions
	 * @param int $maxCount
	 * @param IOperand $parent
	 * @param string $sourceParam
	 * @param int $sourceId
	 * @return IEntityContainer
	 * @throws Exception on wrong restrictions
	 */
	public function getByRestrictions($entityClass, $restrictions, $maxCount = NULL, IOperand $parent = NULL, $sourceParam = NULL, $sourceId = NULL)
	{
		list($entityClass, $entityContainerClass) = $this->extractEntityClasses($entityClass);

		if (!($parent && NULL !== $sourceId && NULL !== $sourceParam)
			&& !(!$parent &&  NULL === $sourceId && NULL === $sourceParam)) {
			throw new Exception('All $parent, $sourceParam and $sourceId must be set or omitted.');
		}

		$parentIdentifier = $parent ? $parent->getIdentifier() : NULL;
		$identifier = $this->serviceAccessor->composeIdentifier($entityClass, TRUE, $parentIdentifier, $sourceParam);

		if ($parent) {
			// has been data already loaded?
			$hierarchy = $parent->getHierarchy()->appendAndCreate($sourceId, $identifier);
			$data = $this->getLoadedData($hierarchy);

			if (FALSE === $data) {
				// try to filter loaded data by ids
				$ids = $this->loadIdsByRestrictions($entityClass, $restrictions, $maxCount);
				$data = $this->getLoadedData($hierarchy, $ids);
			}
		} else {
			$hierarchy = Hierarchy::create($sourceId, $identifier, TRUE);
		}

		if (!isset($data) || FALSE === $data) {
			if (!isset($ids)) {
				$ids = $this->loadIdsByRestrictions($entityClass, $restrictions, $maxCount);
			}

			if (empty($ids)) {
				$data = array();

			} elseif ($suggestor = $this->cache->getCached($identifier, $entityClass, TRUE)) {
				// when no suggestions, even children are ignored, they will be loaded later
				$suggestions = $suggestor->getSuggestions();
				if (empty($suggestions)) {
					$data = array();

				} else {
					$dataHolder = $this->loadDataHolderByMapper($entityClass, $ids, $suggestor);

					if ($dataHolder->hasLoadedChildren()) {
						$this->saveChildren($dataHolder);
					}
					$data = $dataHolder->getParams();
					$this->sortData($ids, $data);
				}

			} else {
				// todo $mapper->siftIds() (sift = protřídit) místo exists() ? - ale potom možná spouštět sortIds, mapper je může zamíchat
				// pomohlo by to v situaci, kdy kontejner nemá permanentně nic zakešovanýho. vždy(*) je třeba zkontrolovat existenci ídéček
				// * když se berou přímo z cizío klíče, nebylo by to třeba, ale to se zde nedozví

				// nonexistent ids prevention
				foreach ($ids as $i => $id) {
					if (!$this->serviceAccessor->getMapper($entityClass)->exists($id)) {
						unset($ids[$i]);
					}
				}
				if ($parent instanceof IEntity && !isset($this->childrenIdentifierList[$identifier->getKey()])) {
					$this->cache->cacheChild($parent->getIdentifier(), $entityClass, $sourceParam, TRUE);
				}
				$data = array_fill_keys($ids, array());
			}

			unset($this->childrenIdentifierList[$identifier->getKey()]);
		}

		if (empty($data)) {
			return array();
		}

		return $this->createEntityContainer($entityContainerClass, $data, $identifier, $entityClass, $hierarchy);
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
			$data = $this->loadDataHolderByMapper($entityClass, $id, $suggestorCached)->getParams() + $data;
		}

		return $this->createEntity($entityClass, $id, $data, $hierarchy);
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
	 * @param Hierarchy $hierarchy
	 * @return IEntity
	 * @todo conditional Entities (e.g. unit with vendor HTC creates Entity HtcUnit, but with same identifier as other)
	 */
	protected function createEntity($entityClass, $id, array $data, Hierarchy $hierarchy = NULL)
	{
		return new $entityClass($id, $data, $this, $hierarchy);
	}


	/**
	 * @param string $containerClass
	 * @param array[] $data
	 * @param IIdentifier $identifier
	 * @param string $entityClass
	 * @param bool[] $hierarchy
	 * @return IEntityContainer
	 */
	protected function createEntityContainer($containerClass, array $data, IIdentifier $identifier, $entityClass, array $hierarchy = NULL)
	{
		return new $containerClass($data, $identifier, $this, $entityClass, $hierarchy);
	}


	/**
	 * @param string $entityClass
	 * @param array $suggestions
	 * @return DataHolder
	 */
	protected function createDataHolder($entityClass, array $suggestions)
	{
		$suggestor = new Suggestor($this->serviceAccessor->getParamMap($entityClass), $this->cache, $suggestions);
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
	 * @param Suggestor $suggestor
	 * @param int $maxCount
	 * @return DataHolder
	 * @throws Exception
	 */
	private function loadDataHolderByMapper($entityClass, $id, Suggestor $suggestor, $maxCount = NULL)
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

		if (!$dataHolder instanceof DataHolder) {
			throw new Exception(get_class($mapper) . "::$m() must return loaded DataHolder instance.");
		}
		return $dataHolder;
	}


	/**
	 * @param IIdentifier $identifier
	 * @param int[] $path
	 * @return bool
	 */
	private function getLoadedData(IIdentifier $identifier, array $path = NULL)
	{
		$key = $identifier->getKey();

		if (isset($this->loadedData[$key])) {
			$data = $this->loadedData[$key];

			if (NULL !== $path) {
				$found = $this->findData($data, $path);
				if (FALSE !== $found) {
					return $found;
				}
			}

			unset($this->loadedData[$key]);
			return $data;
		}
		return FALSE;
	}


	/**
	 * @param IIdentifier $identifier
	 * @param int[] $ids
	 * @return bool
	 */
	private function getLoadedDataByIds(IIdentifier $identifier, array $ids)
	{
		$key = $identifier->getKey();

		if (isset($this->loadedData[$key])) {
			$data = $this->loadedData[$key];

			// nothing is unset because some parent operands can have common children
			returnx array_diff_neboněcotakovýho($ids, $data);
		}
		return FALSE;
	}


	private function findData(array $stack, array $path)
	{
		foreach ($path as $id) {
			if (!isset($stack[$id])) {
				return FALSE;
			}

			$stack = $stack[$id];
		}

		return $stack;
	}


	/**
	 * @todo ale stále je tu problém, že pokud je potomků víc, ale data nenaloadoval do všech
	 * toto proiteruje stejně všechny a ty nenaloadovaný zbytečně
	 * myslim ale že by to mohlo nabízet metodu (nebo nějakym způsobem) pro iteraci jen loadnutých
	 * potomků - ty surový jsou potřeba jen v Mapperu
	 */
	private function saveChildren(DataHolder $dataHolder)
	{
		/** @var DataHolder $child */
		foreach ($dataHolder as $child) {
			if ($child->hasLoadedChildren()) {
				$this->saveChildren($child);
			}

			$data = $child->getParams();
			if (empty($data)) {
				continue;
			}
			$identifier = $child->getSuggestor()->getIdentifier();
			$this->loadedData[$identifier->getKey()] = $data;
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
