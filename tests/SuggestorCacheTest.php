<?php

namespace Shelter\Tests\SuggestorCache;

use Shelter\SuggestorCache;

class Test extends \Shelter\Tests\TestCase
{

	/** @var \Mockery\Mock */
	private $externalCache;

	/** @var \Mockery\Mock */
	private $suggestor;

	/** @var SuggestorCache */
	private $cache;

	/** @var \Mockery\Mock */
	private $paramMap;


	protected function setUp()
	{
		parent::setUp();
		$this->externalCache = \Mockery::mock('Shelter\IExternalCache');
		$requestKey = \Mockery::mock('Shelter\IRequestKey')
			->shouldReceive('getKey')
			->andReturn('')
			->getMock();

		$this->cache = \Mockery::mock('Shelter\SuggestorCache[createSuggestor]', array($this->externalCache, $requestKey))
			->shouldAllowMockingProtectedMethods();
		$this->suggestor = \Mockery::mock('Shelter\ISuggestor');

		$this->paramMap = \Mockery::mock('Shelter\IParamMap')
			->shouldReceive('getMap')
			->andReturn(array('name' => NULL, 'gender' => NULL, 'age' => NULL, 'toy' => NULL, 'intelligence' => NULL))
			->getMock();
	}


	public function testGetCached()
	{
		$cached = array(
			SuggestorCache::PARAM_NAMES => array('name', 'age'),
			SuggestorCache::DESCENDANTS => array(
				array('World\Person', 'partner_id'),
				array('World\Animal', 'pet_id'),
			)
		);

		$this->externalCache
			->shouldReceive('load')
			->once()
			->with('World\Person#0')
			->andReturn($cached);

		$this->cache
			->shouldReceive('createSuggestor')
			->once()
			->with(
				$this->paramMap,
				'World\Person#0',
				$cached[SuggestorCache::PARAM_NAMES],
				$cached[SuggestorCache::DESCENDANTS]
			)
			->andReturn($this->suggestor);

		$suggestor = $this->cache->getCached('World\Person#0', $this->paramMap);
		$this->assertInstanceOf('Shelter\ISuggestor', $suggestor);
	}


	public function testCacheParamName()
	{
		$this->externalCache
			->shouldReceive('load')
			->once()
			->with('World\Person#0')
			->andReturnNull()
			->getMock()
			->shouldReceive('save')
			->once()
			->with('World\Person#0', array(SuggestorCache::PARAM_NAMES => array('gender')));

		$this->cache
			->shouldReceive('createSuggestor')
			->once()
			->with(
				$this->paramMap,
				'World\Person#0',
				array('gender'),
				array()
			)
			->andReturn($this->suggestor);

		$suggestor = $this->cache->cacheParamName('World\Person#0', 'gender', $this->paramMap);
		$this->assertInstanceOf('Shelter\ISuggestor', $suggestor);
	}


	public function testCacheParamNameAddition()
	{
		$cached = $cachingExpected = array(
			SuggestorCache::PARAM_NAMES => array('toy'),
			SuggestorCache::DESCENDANTS => array(
				array('World\Animal', 'pet_id')
			)
		);

		$cachingExpected[SuggestorCache::PARAM_NAMES][] = 'intelligence';

		$this->externalCache
			->shouldReceive('load')
			->once()
			->with('World\Person#0')
			->andReturn($cached)
			->getMock()
			->shouldReceive('save')
			->once()
			->with('World\Person#0', $cachingExpected);

		$this->cache
			->shouldReceive('createSuggestor')
			->once()
			->with(
				$this->paramMap,
				'World\Person#0',
				array('intelligence'),
				array()
			)
			->andReturn($this->suggestor);

		$suggestor = $this->cache->cacheParamName('World\Person#0', 'intelligence', $this->paramMap);
		$this->assertInstanceOf('Shelter\Suggestor', $suggestor);
	}


	public function testCacheDescendant()
	{
		$cachingExpected = $cached = array(
			SuggestorCache::PARAM_NAMES => array('toy'),
			SuggestorCache::DESCENDANTS => array(
				array('World\Animal', 'pet_id')
			)
		);

		$cachingExpected[SuggestorCache::DESCENDANTS][] = array('World\Skill', 'skills');

		$this->externalCache
			->shouldReceive('load')
			->once()
			->with('World\Person#0')
			->andReturn($cached)
			->getMock()
			->shouldReceive('save')
			->once()
			->with('World\Person#0', $cachingExpected);

		$this->cache->cacheDescendant('World\Person#0', 'World\Skill', 'skills');
	}
}
