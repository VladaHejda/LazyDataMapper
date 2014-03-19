<?php

namespace LazyDataMapper;

class EntityServiceAccessor implements IEntityServiceAccessor
{

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
	final public function getParamMap($entityClass)
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
	final public function getMapper($entityClass)
	{
		$mapperName = $this->getMapperClass($entityClass);
		if (!isset($this->mappers[$mapperName])) {
			$this->mappers[$mapperName] = $this->createMapper($mapperName);
		}
		return $this->mappers[$mapperName];
	}


	/**
	 * Tries to create Checker service from classname returned by self::getCheckerClass().
	 * When class does not exist, returns NULL.
	 * @param string $entityClass
	 * @return IChecker|null
	 */
	final public function getChecker($entityClass)
	{
		$checkerName = $this->getCheckerClass($entityClass);
		if (!array_key_exists($checkerName, $this->checkers)) {
			$this->checkers[$checkerName] = class_exists($checkerName) ? $this->createChecker($checkerName) : NULL;
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
		return substr($facadeClass, 0, -6);
	}


	/**
	 * Makes plural (adds "s" at the end) from Entity classname.
	 * For improved solution see for example @link https://gist.github.com/VladaHejda/8775965
	 * @param string $entityClass
	 * @return string
	 */
	public function getEntityContainerClass($entityClass)
	{
		$len = strlen($entityClass);
		if ('y' === $entityClass[$len-1]) {
			$entityClass[$len-1] = 'i';
			return $entityClass . 'es';
		}
		return $entityClass . 's';
	}


	/**
	 * @param string $entityClass
	 * @param bool $isContainer
	 * @param IIdentifier $parentIdentifier
	 * @param string $sourceParam
	 * @return IIdentifier
	 */
	public function composeIdentifier($entityClass, $isContainer = FALSE, IIdentifier $parentIdentifier = NULL, $sourceParam = NULL)
	{
		return new Identifier($entityClass, $isContainer, $parentIdentifier, $sourceParam);
	}


	/**
	 * Adds "ParamMap" at the end of Entity classname.
	 * @param string $entityClass
	 * @return string
	 */
	protected function getParamMapClass($entityClass)
	{
		return $entityClass . 'ParamMap';
	}


	/**
	 * Adds "Mapper" at the end of Entity classname.
	 * @param string $entityClass
	 * @return string
	 */
	protected function getMapperClass($entityClass)
	{
		return $entityClass . 'Mapper';
	}


	/**
	 * Adds "Checker" at the end of Entity classname.
	 * @param string $entityClass
	 * @return string
	 */
	protected function getCheckerClass($entityClass)
	{
		return $entityClass . 'Checker';
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
