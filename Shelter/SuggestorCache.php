<?php

namespace Shelter;

class SuggestorCache implements ISuggestorCache
{

	/**
	 * @param IExternalCache $cache
	 * @param IRequestKey $requestKey
	 */
	public function __construct(IExternalCache $cache, IRequestKey $requestKey)
	{
	}


	/**
	 * @param string $identifier
	 * @param string $paramName
	 * @param IParamMap $map
	 * @return ISuggestor
	 */
	public function cacheParamName($identifier, $paramName, IParamMap $map)
	{
	}


	/**
	 * @param string $identifier
	 * @param string $descendantEntityClass
	 * @param string $sourceParam
	 * @return void
	 */
	public function cacheDescendant($identifier, $descendantEntityClass, $sourceParam = NULL)
	{
	}


	/**
	 * @todo identifier would be given in object.. ?
	 * @param string $identifier
	 * @param IParamMap $map
	 * @return ISuggestor
	 */
	public function getCached($identifier, IParamMap $map)
	{
	}


	/**
	 * @param IParamMap $paramMap
	 * @param string $identifier
	 * @param array $suggestions
	 * @param array $descendants
	 * @return ISuggestor
	 */
	protected function createSuggestor(IParamMap $paramMap, $identifier, array $suggestions, array $descendants = array())
	{
	}
}
