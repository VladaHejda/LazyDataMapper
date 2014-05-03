<?php

namespace LazyDataMapper;

/**
 * Based on Suggestor gains data from Mapper and gives data to Mapper's method save() and create().
 * @todo rename "params" to "data"
 * @todo asi se zbavit Holderu v Mapperu (stejně už data nekontroluje, jen ořezává, mohl by si ho Accessor nastavovat sám
 */
class DataHolder implements \Iterator
{

	/** @var array */
	protected $data = array();

	/** @var array */
	protected $children = array();

	/** @var Suggestor */
	protected $suggestor;

	/** @var self */
	protected $parent;

	/** @var string */
	protected $idSource, $parentIdSource;

	/** @var array[] parent_id => [child_id, child_id, ...] */
	protected $relations;

	/** @var array */
	private $idList;


	/**
	 * @param Suggestor $suggestor
	 * @param self $parent
	 * @throws Exception
	 * @todo zajistit: jakmile je jedinej parent collection, je již collection vše následující
	 * @todo co když $ids bude prázdný array (resp. mapper zjistí že žádní potomci nejsou)
	 */
	public function __construct(Suggestor $suggestor, self $parent = NULL)
	{
		$this->suggestor = $suggestor;
		$this->parent = $parent;
	}


	/**
	 * @return self
	 */
	public function getParent()
	{
		return $this->parent;
	}


	/**
	 * @param string $source
	 * @return self
	 * @throws Exception
	 */
	public function setIdSource($source)
	{
		if (!$this->getSuggestor()->isCollection()) {
			throw new Exception('Simple DataHolder does not need ids.');
		}
		$this->idSource = $source;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getIdSource()
	{
		return $this->idSource;
	}


	/**
	 * @param $source string
	 * @return self
	 * @throws Exception
	 */
	public function setParentIdSource($source)
	{
		if ($this->relations !== NULL) {
			throw new Exception('Parent ids are already set.');
		}
		if (!$this->parent || !$this->parent->getSuggestor()->isCollection()) {
			throw new Exception('Root DataHolder or DataHolder with simple parent does not need parent ids.');
		}
		$this->parentIdSource = $source;
		return $this;
	}


	/**
	 * @param int $childId
	 * @param int $parentId
	 * @return self
	 * @throws Exception
	 */
	public function setRelation($childId, $parentId)
	{
		if (!$this->parent || !$this->parent->getSuggestor()->isCollection()) {
			throw new Exception('Root DataHolder or DataHolder with simple parent does not need parent ids.');
		}
		if (!isset($this->relations[$parentId])) {
			$this->relations[$parentId] = array();
		}
		if (!in_array($childId, $this->relations[$parentId])) {
			$this->relations[$parentId][] = $childId;
			$this->idList[$childId] = TRUE;
		}
		return $this;
	}


	/**
	 * @param array $idsVsIds child_id => parent_id
	 * @return self
	 */
	public function setParentIds(array $idsVsIds)
	{
		foreach ($idsVsIds as $childId => $parentId) {
			if (is_array($parentId)) {
				foreach ($parentId as $parentSubId) {
					// todo maybe solve this another way - current iterates a lot
					$this->setRelation($childId, $parentSubId);
				}
			} else {
				$this->setRelation($childId, $parentId);
			}
			$this->setRelation($childId, $parentId);
		}
		return $this;
	}


	/**
	 * @param array $idsVsIds parent_id => child_id
	 * @return self
	 */
	public function setChildrenIds(array $idsVsIds)
	{
		foreach ($idsVsIds as $parentId => $childId) {
			if (is_array($childId)) {
				foreach ($childId as $childSubId) {
					$this->setRelation($childSubId, $parentId);
				}
			} else {
				$this->setRelation($childId, $parentId);
			}
		}
		return $this;
	}


	/**
	 * @return array
	 */
	public function getRelations()
	{
		return $this->relations;
	}


	/**
	 * @param array|array[] $data when no id source, id is expected in index
	 * @return self
	 * @throws Exception
	 * @todo vyřešit situaci, kdy je potomek prázdný (parent id je NULL) !!!
	 */
	public function setParams(array $data)
	{
		$suggestions = array_fill_keys($this->suggestor->getSuggestions(), TRUE);

		// collective
		if ($this->suggestor->isCollection()) {
			// parent id source
			$parentSource = NULL;
			$needParentIds = $this->parent && $this->parent->getSuggestor()->isCollection();

			if ($this->relations === NULL && $needParentIds) {
				if ($this->parentIdSource !== NULL) {
					$parentSource = $this->parentIdSource;
				} else {
					$parentSource = $this->parent->getIdSource();

					if ($parentSource === NULL) {
						throw new Exception('You need to set parent ids or parent id source before you set data to collective DataHolder');
					}
				}
			}

			foreach ($data as $id => $subdata) {
				if (!is_array($subdata)) {
					if ($this->idSource !== NULL) {
						$subdata = array($subdata);
					} else {
						throw new Exception('Only array of data arrays can be given to collective DataHolder.');
					}
				}

				// id
				if ($this->idSource !== NULL) {
					if (!isset($subdata[$this->idSource])) {
						throw new Exception("One of array members does not have id source column ($this->idSource).");
					}
					$id = $subdata[$this->idSource];
				}

				if (!is_numeric($id)) {
					throw new Exception("Id '$id' does not seem to be id.");
				}

				// parent id
				if ($needParentIds) {
					if ($parentSource !== NULL) {
						if (!isset($subdata[$parentSource])) {
							throw new Exception("One of array members does not have parent id source column ($parentSource).");
						}

						$parentId = $subdata[$parentSource];
						if (!is_numeric($parentId)) {
							throw new Exception("Parent id '$parentId' does not seem to be id.");
						}
						$this->setRelation($id, $parentId);

					} else {
						if (!isset($this->idList[$id])) {
							throw new Exception("Id $id does not exist in id list.");
						}
					}
				}

				// set data
				if (!isset($this->data[$id])) {
					$this->data[$id] = array();
				}
				$subdata = array_intersect_key($subdata, $suggestions);
				$this->data[$id] = $subdata + $this->data[$id];
			}

		// simple
		} else {
			$data = array_intersect_key($data, $suggestions);
			$this->data = $data + $this->data;
		}

		return $this;
	}


	/**
	 * @param string $group
	 * @return array
	 */
	public function getParams($group = NULL)
	{
		if (NULL === $group) {
			return $this->data;
		}

		$map = $this->suggestor->getParamMap()->getMap($group);
		if ($this->suggestor->isCollection()) {
			$collectionMap = array();
			foreach ($this->data as $id => $data) {
				$collectionMap[$id] = $this->fillMap($map, $data);
			}
			return $collectionMap;
		}
		return $this->fillMap($map, $this->data);
	}


	/**
	 * @return bool
	 */
	public function isDataLoaded()
	{
		return (bool) $this->data;
	}


	/**
	 * @param string $group
	 * @return bool
	 * @throws Exception on unknown group
	 */
	public function isDataInGroup($group)
	{
		$map = $this->suggestor->getParamMap()->getMap($group, FALSE);
		if ($this->suggestor->isCollection()) {
			foreach ($this->data as $data) {
				$isDataInGroup = (bool) array_intersect(array_keys($data), $map);
				if ($isDataInGroup) {
					return TRUE;
				}
			}
			return FALSE;
		}
		return (bool) array_intersect(array_keys($this->data), $map);
	}


	/**
	 * @param string $sourceParam
	 * @return self|null
	 * @throws Exception
	 */
	public function getChild($sourceParam)
	{
		if (array_key_exists($sourceParam, $this->children)) {
			return $this->children[$sourceParam];
		}

		$suggestor = $this->suggestor->getChild($sourceParam);
		if (!$suggestor) {
			return NULL;
		}

		$child = new self($suggestor, $this);
		$this->children[$sourceParam] = $child;
		return $child;
	}


	/**
	 * @see getChild()
	 */
	public function __get($sourceParam)
	{
		return $this->getChild($sourceParam);
	}


	/**
	 * Says whether children was loaded, not whether they exist. For that @see Suggestor::hasChildren
	 * @return bool
	 */
	public function hasLoadedChildren()
	{
		return !empty($this->children);
	}


	/**
	 * @return Suggestor
	 */
	public function getSuggestor()
	{
		return $this->suggestor;
	}


	public function rewind()
	{
		$this->suggestor->rewind();
	}


	public function valid()
	{
		return $this->suggestor->valid();
	}


	/**
	 * @return self
	 */
	public function current()
	{
		$key = $this->suggestor->key();
		if (array_key_exists($key, $this->children)) {
			return $this->children[$key];
		}
		$suggestor = $this->suggestor->current();
		if (!$suggestor) {
			return FALSE;
		}

		return $this->children[$key] = new self($suggestor, $this);
	}


	public function key()
	{
		return $this->suggestor->key();
	}


	public function next()
	{
		$this->suggestor->next();
	}


	public function checkCompleteness()
	{
		// todo - Accessor will call this method to test if all relations have data loaded
	}


	private function fillMap(array $map, array $data)
	{
		foreach ($map as $paramName => & $value) {
			if (array_key_exists($paramName, $data)) {
				$value = $data[$paramName];
			} else {
				unset ($map[$paramName]);
			}
		}
		return $map;
	}
}
