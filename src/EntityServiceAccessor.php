<?php

namespace LazyDataMapper;

class EntityServiceAccessor implements IEntityServiceAccessor
{

	protected $errorInstructions = 'Check classname, solve loading or change EntityServiceAccessor classnames conventions.';

	/** @var ParamMap[] */
	private $paramMaps = array();

	/** @var IMapper[] */
	private $mappers = array();

	/** @var IChecker[] */
	private $checkers = array();


	/**
	 * Creates ParamMap service from classname returned by self::getParamMapClass().
	 * @param string $entityClass
	 * @return ParamMap
	 */
	public function getParamMap($entityClass)
	{
		$mapName = $this->getParamMapClass($entityClass);
		if (!isset($this->paramMaps[$mapName])) {
			$this->paramMaps[$mapName] = $this->createParamMap($mapName);
		}
		return $this->paramMaps[$mapName];
	}


	/**
	 * Creates Mapper service from classname returned by self::getMapperClass().
	 * @param string $entityClass
	 * @return IMapper
	 */
	public function getMapper($entityClass)
	{
		$mapperName = $this->getMapperClass($entityClass);
		if (!isset($this->mappers[$mapperName])) {
			$this->mappers[$mapperName] = $this->createMapper($mapperName);
		}
		return $this->mappers[$mapperName];
	}


	/**
	 * Creates Checker service from classname returned by self::getCheckerClass(), if class exists.
	 * When class does not exist, returns NULL.
	 * @param string $entityClass
	 * @return IChecker|null
	 */
	public function getChecker($entityClass)
	{
		$checkerName = $this->getCheckerClass($entityClass);
		if (!array_key_exists($checkerName, $this->checkers)) {
			$this->checkers[$checkerName] = $checkerName === NULL ? NULL : $this->createChecker($checkerName);
		}
		return $this->checkers[$checkerName];
	}


	/**
	 * Cut "Facade" from Facade classname.
	 * @param Facade $facade
	 * @return string
	 * @throws Exception
	 */
	public function getEntityClass(Facade $facade)
	{
		$facadeClass = get_class($facade);
		if (strcasecmp(substr($facadeClass, -6), 'facade')) {
			throw new Exception("Expected Facade with classname <EntityName>Facade. $facadeClass given.");
		}
		$class = substr($facadeClass, 0, -6);
		if (substr($class, -1) === '\\') {
			$class = substr($class, 0, -1);
		}

		if (!class_exists($class)) {
			throw new Exception("Entity class '$class' does not exist. " . $this->errorInstructions);
		}
		return $class;
	}


	/**
	 * Makes plural (adds "s" at the end) from Entity classname.
	 * For improved solution see for example @link https://gist.github.com/VladaHejda/8775965
	 * @param string $entityClass
	 * @return string
	 * @throws Exception
	 */
	public function getEntityCollectionClass($entityClass)
	{
		$len = strlen($entityClass);
		if ('y' === $entityClass[$len-1]) {
			$entityClass[$len-1] = 'i';
			return $entityClass . 'es';
		}
		$class = $entityClass . 's';

		if (!class_exists($class)) {
			throw new Exception("EntityCollection class '$class' does not exist. " . $this->errorInstructions);
		}
		return $class;
	}


	/**
	 * @param string $entityClass
	 * @param string $origin
	 * @param IIdentifier $parentIdentifier
	 * @param string $sourceParam
	 * @return IIdentifier
	 */
	public function composeIdentifier($entityClass, $origin = IIdentifier::BY_ID, IIdentifier $parentIdentifier = NULL, $sourceParam = NULL)
	{
		return new Identifier($entityClass, $origin, $parentIdentifier, $sourceParam);
	}


	/**
	 * @param Accessor $accessor
	 * @param string $entityClass
	 * @param int $id
	 * @param array $data
	 * @param IIdentifier $identifier
	 * @return mixed
	 */
	public function createEntity(Accessor $accessor, $entityClass, $id, array $data, IIdentifier $identifier = NULL)
	{
		return new $entityClass($id, $data, $accessor, $identifier);
	}


	/**
	 * @param Accessor $accessor
	 * @param string $collectionClass
	 * @param array[] $data
	 * @param IIdentifier $identifier
	 * @param string $entityClass
	 * @return IEntityCollection
	 */
	public function createEntityCollection(Accessor $accessor, $collectionClass, array $data, IIdentifier $identifier, $entityClass)
	{
		return new $collectionClass($data, $identifier, $accessor, $entityClass);
	}


	/**
	 * @param string $entityClass
	 * @param SuggestorCache $suggestorCache
	 * @param array $suggestions
	 * @return Suggestor
	 */
	public function createSuggestor($entityClass, SuggestorCache $suggestorCache, array $suggestions)
	{
		return new Suggestor($this->getParamMap($entityClass), $suggestorCache, $suggestions);
	}


	/**
	 * @param Suggestor $suggestor
	 * @return DataHolder
	 */
	public function createDataHolder(Suggestor $suggestor)
	{
		return new DataHolder($suggestor);
	}


	/**
	 * Adds "ParamMap" at the end of Entity classname.
	 * @param string $entityClass
	 * @return string
	 * @throws Exception
	 */
	protected function getParamMapClass($entityClass)
	{
		if (class_exists($class = $entityClass . 'ParamMap')) {
			return $class;
		}
		if (class_exists($class2 = $entityClass . '\ParamMap')) {
			return $class2;
		}
		throw new Exception("ParamMap classes '$class' or '$class2' do not exist. " . $this->errorInstructions);
	}


	/**
	 * Adds "Mapper" at the end of Entity classname.
	 * @param string $entityClass
	 * @return string
	 * @throws Exception
	 */
	protected function getMapperClass($entityClass)
	{
		if (class_exists($class = $entityClass . 'Mapper')) {
			return $class;
		}
		if (class_exists($class2 = $entityClass . '\Mapper')) {
			return $class2;
		}
		throw new Exception("Mapper classes '$class' or '$class2' do not exist. " . $this->errorInstructions);
	}


	/**
	 * Adds "Checker" at the end of Entity classname.
	 * @param string $entityClass
	 * @return string
	 */
	protected function getCheckerClass($entityClass)
	{
		if (class_exists($class = $entityClass . 'Checker')) {
			return $class;
		}
		if (class_exists($class2 = $entityClass . '\Checker')) {
			return $class2;
		}
		return NULL;
	}


	protected function createParamMap($mapName)
	{
		return new $mapName;
	}


	protected function createMapper($mapperName)
	{
		return new $mapperName;
	}


	protected function createChecker($checkerName)
	{
		return new $checkerName;
	}
}
