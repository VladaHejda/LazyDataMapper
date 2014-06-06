<?php

namespace LazyDataMapper;

/**
 * The main class leading all dependencies. Everything there is controlled internally.
 * @todo why id must necessary be integer?
 * @todo baseNamespace setting? It could be for example AppName\Entities - will be added during new instance creating, but not stored to cache
 */
final class Accessor
{

	const ALL = '*';

	/** @var SuggestorCache */
	protected $cache;

	/** @var IEntityServiceAccessor */
	protected $serviceAccessor;

	/** @var array */
	private $loadedData = array(), $dataRelations = array();

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
	 * @param string $entityClass
	 * @param int|IRestrictor $id
	 * @param IEntity $parent
	 * @param string $sourceParam
	 * @return IEntity
	 * @throws Exception
	 */
	public function getEntity($entityClass, $id, IEntity $parent = NULL, $sourceParam = NULL)
	{
		if (($parent && NULL === $sourceParam) || (NULL !== $sourceParam && !$parent)) {
			throw new Exception('Both $parent and $sourceParam must be set or omitted.');
		}

		$origin = $id instanceof IRestrictor ? IIdentifier::ONE_BY_RESTRICTIONS : IIdentifier::BY_ID;
		$parentIdentifier = $parent ? $parent->getIdentifier() : NULL;
		$identifier = $this->serviceAccessor->composeIdentifier($entityClass, $origin, $parentIdentifier, $sourceParam);

		if ($parent && $data = $this->getLoadedData($identifier, $parent->getId())) {
			if ($data === TRUE) {
				// data loaded, but empty
				return NULL;
			}
			// else $data set

		} else {
			if ($id instanceof IRestrictor) {
				$id = $this->loadIdsByRestrictions($id, $entityClass);
				$count = count($id);
				if (!$count) {
					return NULL;

				} elseif ($count > 1) {
					throw new Exception("If given to Facade's getOneByRestrictions() method,"
						. " Restrictor must match at most one item. However $count items returned.");
				}

				$id = reset($id);

			} elseif (!$this->serviceAccessor->getMapper($entityClass)->exists($id)) {
				return NULL;
			}

			if ($suggestor = $this->cache->getCached($identifier, $entityClass, FALSE, $childrenIdentifierList)) {
				$data = $this->loadData($entityClass, $id, $suggestor);

			} else {
				if ($parent && !isset($this->childrenIdentifierList[$identifier->getKey()])) {
					$this->cache->cacheChild($parentIdentifier, $entityClass, $sourceParam, $origin);
				}
				$data = array();
			}

			unset($this->childrenIdentifierList[$identifier->getKey()]);

			if (!empty($childrenIdentifierList)) {
				$this->childrenIdentifierList += array_fill_keys($childrenIdentifierList, TRUE);
			}
		}

		// todo conditional Entities (e.g. unit with vendor HTC creates Entity HtcUnit, but with same identifier as other)
		return $this->serviceAccessor->createEntity($this, $entityClass, $id, $data, $identifier);
	}


	/**
	 * @param array $entityClasses
	 * @param IRestrictor|int[] $restrictions
	 * @param IEntity $parent
	 * @param string $sourceParam
	 * @param int $maxCount
	 * @param int $page
	 * @param bool &$exceeded
	 * @return IEntityCollection
	 * @throws Exception on wrong restrictions
	 */
	public function getCollection(array $entityClasses, $restrictions, IEntity $parent = NULL, $sourceParam = NULL, $maxCount = NULL, $page = NULL, &$exceeded = NULL)
	{
		$entityClass = array_shift($entityClasses);
		if (!count($entityClasses)) {
			$entityCollectionClass = $this->serviceAccessor->getEntityCollectionClass($entityClass);
		} else {
			$entityCollectionClass = array_shift($entityClasses);
		}

		if (($parent && NULL === $sourceParam) || (NULL !== $sourceParam && !$parent)) {
			throw new Exception('Both $parent and $sourceParam must be set or omitted.');
		}

		if ($restrictions === self::ALL) {
			$origin = IIdentifier::ALL;
		} elseif ($restrictions instanceof IRestrictor) {
			$origin = IIdentifier::BY_RESTRICTIONS;
		} else {
			$origin = IIdentifier::BY_IDS_RANGE;
		}
		$parentIdentifier = $parent ? $parent->getIdentifier() : NULL;
		$identifier = $this->serviceAccessor->composeIdentifier($entityClass, $origin, $parentIdentifier, $sourceParam);

		if ($parent && $data = $this->getLoadedData($identifier, $parent->getId(), TRUE)) {
			if ($data === TRUE) {
				// data loaded, but empty
				return array();
			}
			// else $data set

		} else {
			$ids = $this->loadIds($restrictions, $entityClass, $maxCount, $page, $exceeded);

			if (empty($ids)) {
				$data = array();

			} elseif ($suggestor = $this->cache->getCached($identifier, $entityClass, TRUE, $childrenIdentifierList)) {
				$data = $this->loadData($entityClass, $ids, $suggestor);

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
				if ($parent && !isset($this->childrenIdentifierList[$identifier->getKey()])) {
					$this->cache->cacheChild($parentIdentifier, $entityClass, $sourceParam, $origin);
				}
				$data = array_fill_keys($ids, array());
			}

			unset($this->childrenIdentifierList[$identifier->getKey()]);

			if (!empty($childrenIdentifierList)) {
				$this->childrenIdentifierList += array_fill_keys($childrenIdentifierList, TRUE);
			}
		}

		if (empty($data)) {
			return array();
		}

		return $this->serviceAccessor->createEntityCollection($this, $entityCollectionClass, $data, $identifier, $entityClass);
	}


	/**
	 * @param string $entityClass
	 * @param array $publicData
	 * @param array $privateData
	 * @param bool $throwFirst
	 * @return IEntity
	 * @throws Exception
	 */
	public function create($entityClass, array $publicData, array $privateData = array(), $throwFirst = TRUE)
	{
		$entity = $this->serviceAccessor->createEntity($this, $entityClass, NULL, $privateData);

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
		$dataHolder = $this->serviceAccessor->createDataHolder(
			$this->serviceAccessor->createSuggestor($entityClass, $this->cache, array_keys($data))
		);
		$dataHolder->setData($data);

		$mapper = $this->serviceAccessor->getMapper($entityClass);
		$id = $mapper->create($dataHolder);
		if (!is_int($id)) {
			throw new Exception(get_class($mapper) . '::create() must return integer ID.');
		}
		$identifier = $this->serviceAccessor->composeIdentifier($entityClass, IIdentifier::CREATE);

		if ($suggestorCached = $this->cache->getCached($identifier, $entityClass)) {
			$data = $this->loadDataHolderByMapper($entityClass, $id, $suggestorCached)->getData() + $data;
		}

		return $this->serviceAccessor->createEntity($this, $entityClass, $id, $data, $identifier);
	}


	/**
	 * @param string $entityClass
	 * @param int $id
	 */
	public function remove($entityClass, $id)
	{
		$this->serviceAccessor->getMapper($entityClass)->remove($id);
	}


	/**
	 * @param string $entityClass
	 * @param IRestrictor|int[] $restrictions
	 * @throws Exception
	 */
	public function removeByRestrictions($entityClass, $restrictions)
	{
		$ids = $this->loadIds($restrictions, $entityClass);

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
		$suggestor = $this->cache->cacheSuggestion($entity->getIdentifier(), $paramName, $entityClass);
		$dataHolder = $this->loadDataHolderByMapper($entityClass, $entity->getId(), $suggestor);
		$params = $dataHolder->getData();
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
		$dataHolder = $this->serviceAccessor->createDataHolder(
			$this->serviceAccessor->createSuggestor($entityClass, $this->cache, array_keys($data))
		);
		$dataHolder->setData($data);

		$this->serviceAccessor->getMapper($entityClass)->save($entity->getId(), $dataHolder);
	}


	/********************* internal *********************/


	/**
	 * @param IRestrictor|int[] $restrictions
	 * @param string $entityClass
	 * @param int $maxCount
	 * @param int $page
	 * @param bool &$exceeded
	 * @return int[]
	 * @throws Exception
	 * @throws TooManyItemsException
	 */
	private function loadIds($restrictions, $entityClass, $maxCount = NULL, $page = NULL, &$exceeded = NULL)
	{
		$cannotExceed = $exceeded = FALSE;

		if ($maxCount !== NULL) {
			$maxCount = (int) $maxCount;

			if ($page !== NULL) {
				$page = (int) $page;
				if ($page < 1) {
					$page = 1;
				}
			} else {
				// if $page is NULL, result row count cannot exceed $maxCount
				$cannotExceed = TRUE;
			}
		} else {
			$page = NULL;
		}

		if (is_array($restrictions)) {
			return $restrictions;

		} elseif ($restrictions instanceof IRestrictor || $restrictions === self::ALL) {
			$ids = $this->loadIdsByRestrictions($restrictions, $entityClass, $maxCount, $page);

			$exceeded = $maxCount !== NULL && count($ids) > $maxCount;

			if ($exceeded) {
				if ($cannotExceed) {
					throw new TooManyItemsException("Trying to get more than $maxCount pieces of $entityClass."
						. " Increase the \$maxCount limit or restrict result more.");
				}
				array_pop($ids);
			}

			return $ids;

		} else {
			throw new Exception('Expected instance of IRestrictor or an array.');
		}
	}


	/**
	 * @param IRestrictor $restrictions
	 * @param $entityClass
	 * @param int $maxCount
	 * @param int $page
	 * @return int[]
	 * @throws Exception
	 */
	private function loadIdsByRestrictions($restrictions, $entityClass, $maxCount = NULL, $page = NULL)
	{
		$offset = 0;
		if ($maxCount !== NULL) {
			if ($page !== NULL) {
				--$page;
				$offset = $page * $maxCount;
			}
			++$maxCount;
		}
		$mapper = $this->serviceAccessor->getMapper($entityClass);
		if ($restrictions === self::ALL) {
			$m = 'getAllIds';
			$ids = $mapper->getAllIds($maxCount, $offset);
		} else {
			$m = 'getIdsByRestrictions';
			$ids = $mapper->getIdsByRestrictions($restrictions, $maxCount, $offset);
		}
		if (NULL === $ids) {
			return array();

		} elseif (!is_array($ids)) {
			throw new Exception(get_class($mapper) . "::$m() must return array or null.");
		}

		return $ids;
	}


	/**
	 * @param string $entityClass
	 * @param int|int[] $id
	 * @param Suggestor $suggestor
	 * @return array
	 */
	private function loadData($entityClass, $id, Suggestor $suggestor)
	{
		$isCollection = is_array($id);

		// when no suggestions, even children are ignored, they will be loaded later
		$suggestions = $suggestor->getSuggestions();
		if (empty($suggestions)) {
			return $isCollection ? array_fill_keys($id, array()) : array();
		}
		$dataHolder = $this->loadDataHolderByMapper($entityClass, $id, $suggestor);
		if ($dataHolder->hasLoadedChildren()) {
			$this->saveChildren($dataHolder);
		}
		$data = $dataHolder->getData();
		if ($isCollection) {
			$this->sortData($id, $data);
		}
		return $data;
	}


	/**
	 * @param string $entityClass
	 * @param int|int[] $id or ids
	 * @param Suggestor $suggestor
	 * @return DataHolder
	 * @throws Exception
	 */
	private function loadDataHolderByMapper($entityClass, $id, Suggestor $suggestor)
	{
		$isCollection = is_array($id);
		$mapper = $this->serviceAccessor->getMapper($entityClass);
		$datHolder = $this->serviceAccessor->createDataHolder($suggestor);
		$m = $isCollection ? 'getByIdsRange' : 'getById';
		$dataHolder = $mapper->$m($id, $suggestor, $datHolder);

		if (!$dataHolder instanceof DataHolder) {
			throw new Exception(get_class($mapper) . "::$m() must return loaded DataHolder instance.");
		}
		return $dataHolder;
	}


	private function getLoadedData(IIdentifier $identifier, $parentId, $isCollection = FALSE)
	{
		$key = $identifier->getKey();
		if (isset($this->loadedData[$key])) {
			if (isset($this->dataRelations[$key])) {
				if (!isset($this->dataRelations[$key][$parentId])) {
					// empty
					return TRUE;
				}
				$childrenIds = $this->dataRelations[$key][$parentId];
				$data = array_intersect_key($this->loadedData[$key], array_flip($childrenIds));
				if (!$isCollection) {
					$data = reset($data);
				} else {
					$data = $this->sortData($childrenIds, $data);
				}
				// todo uklidit (ale jedno child může mít víc rodičů - na to bacha)
			} else {
				$data = $this->loadedData[$key];
				unset($this->loadedData[$key]);
			}
			return $data;
		}
		return FALSE;
	}


	/**
	 * @todo ale stále je tu problém, že pokud je potomků víc, ale data nenaloadoval do všech
	 * toto proiteruje stejně všechny a ty nenaloadovaný zbytečně
	 * myslim ale že by to mohlo nabízet metodu (nebo nějakym způsobem) pro iteraci jen loadnutých
	 * potomků - ty surový jsou potřeba jen v Mapperu
	 *
	 * CO KDYBY TEN ITERÁTOR ITEROVAL JEN LOADNUTÝ? je možnost že by ho použil v Mapperu?
	 */
	private function saveChildren(DataHolder $dataHolder)
	{
		foreach ($dataHolder as $child) {
			if ($child->hasLoadedChildren()) {
				$this->saveChildren($child);
			}

			// todo - viz todo nad tímto todo
			// může dojít k tomu, že sice child načte ale neexistují pro něj žádná data
			// potom je zde vráceno prázné array a nic neuloženo
			// ovšem nyní nerozezná od neloadnutých holdrů a prázdných holdrů - kdyby se implementovalo to todo nad tim
			// mohlo by to klidně kešovat i prázná data - ušetřilo by se dvojí tahání (při prázdnym výsledku)
			$data = $child->getData();
			if (empty($data)) {
				continue;
			}
			$identifier = $child->getSuggestor()->getIdentifier();
			$key = $identifier->getKey();
			$this->loadedData[$key] = $data;
			$relations = $child->getRelations();
			if ($relations) {
				$this->dataRelations[$key] = $relations;
			}
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
}
