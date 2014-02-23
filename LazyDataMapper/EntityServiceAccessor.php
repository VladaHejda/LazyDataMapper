<?php

namespace LazyDataMapper;

class EntityServiceAccessor implements IEntityServiceAccessor
{

	/** @var IParamMap[] */
	protected $paramMaps = array();

	/** @var IMapper[] */
	protected $mappers = array();

	/** @var IChecker[] */
	protected $checkers = array();


	/**
	 * Creates ParamMap service from classname created from Entity classname
	 * by adding "ParamMap" at the end of it.
	 * @param string $entityClass
	 * @return IParamMap
	 */
	public function getParamMap($entityClass)
	{
		$mapName = $entityClass . 'ParamMap';
		if (!isset($this->paramMaps[$mapName])) {
			$this->paramMaps[$mapName] = new $mapName;
		}
		return $this->paramMaps[$mapName];
	}


	/**
	 * Creates Mapper service from classname created from Entity classname
	 * by adding "Mapper" at the end of it.
	 * @param string $entityClass
	 * @return IMapper
	 */
	public function getMapper($entityClass)
	{
		$mapperName = $entityClass . 'Mapper';
		if (!isset($this->mappers[$mapperName])) {
			$this->mappers[$mapperName] = new $mapperName;
		}
		return $this->mappers[$mapperName];
	}


	/**
	 * Tries to create Checker service from classname created from Entity classname
	 * by adding "Checker" at the end of it. When class does not exists, returns NULL.
	 * @param string $entityClass
	 * @return IChecker|null
	 */
	public function getChecker($entityClass)
	{
		$checkerName = $entityClass . 'Checker';
		if (!array_key_exists($checkerName, $this->checkers)) {
			$this->checkers[$checkerName] = class_exists($checkerName) ? new $checkerName : NULL;
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
	 * For better results see for example @link https://gist.github.com/VladaHejda/8775965
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
}
