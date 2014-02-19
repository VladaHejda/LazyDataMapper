<?php

namespace Shelter\Tests;

use Shelter;

require_once __DIR__ . '/default.php';
require_once __DIR__ . '/serviceAccessor.php';

class Icebox extends Shelter\Entity
{

	protected $privateParams = ['repairs'];


	protected function getFood($food)
	{
		if (empty($food)) {
			return [];
		}
		return explode('|', $food);
	}


	protected function getFreezer($freezer)
	{
		return (bool) $freezer;
	}


	protected function getCapacity($capacity, $unit = 'l')
	{
		$capacity = (int) $capacity;
		switch ($unit) {
			case 'l':
				return $capacity;
			case 'ml':
				return $capacity *1000;
		}
	}


	protected function getFreezerCapacity($unit = 'l')
	{
		if (!$this->freezer) {
			return 0;
		}
		$capacity = (int) $this->getClear('freezer');
		switch ($unit) {
			case 'l':
				return $capacity;
			case 'ml':
				return $capacity *1000;
		}
	}


	protected function getDescription()
	{
		return ucfirst($this->color) . " icebox, $this->capacity l.";
	}


	protected function getTaggedDescription()
	{
		return "<p>$this->description</p>";
	}


	protected function getRepaired()
	{
		return (bool) $this->getClear('repairs');
	}


	protected function setColor($color)
	{
		return (string) $color;
	}


	protected function setCapacity($capacity)
	{
		return (int) $capacity;
	}


	protected function setFreezerCapacity($capacity)
	{
		$capacity = (int) $capacity;
		if (!$capacity) {
			$capacity = '';
		}
		$this->setReadOnlyOrPrivate('freezer', $capacity);
	}


	public function addRepair()
	{
		$this->setReadOnlyOrPrivate('repairs', (int) $this->getClear('repairs') +1);
		return $this->getClear('repairs');
	}


	public function addFood($food)
	{
		$foods = $this->getClear('food');
		if (!empty($foods)) {
			$foods .= '|';
		}
		$this->setReadOnlyOrPrivate('food', $foods . $food);
	}
}


class Iceboxes extends Shelter\EntityContainer
{

	protected function getCapacity()
	{
		$total = 0;
		foreach ($this->getParams('capacity') as $capacity) {
			$total += $capacity;
		}
		return $total;
	}
}


class IceboxFacade extends Shelter\Facade
{

	protected $entityClass = ['Shelter\Tests\Icebox', 'Shelter\Tests\Iceboxes'];
}


class IceboxRestrictor extends Shelter\FilterRestrictor
{

	public function limitCapacity($min, $max = NULL)
	{
		$this->inRange('capacity', $min, $max);
	}


	public function limitColor($color, $deny = FALSE)
	{
		if ($deny) {
			$this->notEquals('color', $color);
		} else {
			$this->equals('color', $color);
		}
	}


	public function limitFood($food, $deny = FALSE)
	{
		$pattern = $deny ? $this->getNotMatch('food') : $this->getMatch('food');
		if (empty($pattern)) {
			$pattern = "/\b($food)\b/";
		} else {
			$pattern = str_replace(')', "|$food)", $pattern);
		}

		if ($deny) {
			$this->notMatch('food', $pattern);
		} else {
			$this->match('food', $pattern);
		}
	}
}


class IceboxParamMap extends Shelter\ParamMap
{

	protected $map = ['color', 'capacity', 'freezer', 'food', 'repairs', ];
}


class IceboxChecker extends Shelter\Checker
{

	protected function checkUpdate(Shelter\IEntity $icebox)
	{
		$this->checkRequired(['color']);
		$this->addCheck('integrity');
	}


	protected function checkCreate(Shelter\IDataHolder $holder)
	{
		$this->addCheck('integrity');

		if ($holder->color == 'nice') {
			$this->addError('Nice is not a color!');
		}
	}


	protected function checkIntegrity(Shelter\IDataEnvelope $subject)
	{
		if (count($subject->food) > 4 && $subject->capacity < 20) {
			$this->addError("Not enough space in icebox.");
		}
	}
}


class IceboxMapper extends defaultMapper
{

	public static $calledGetById = 0;
	public static $calledGetByRestrictions = 0;

	/** @var Shelter\ISuggestor */
	public static $lastSuggestor;

	/** @var Shelter\IDataHolder */
	public static $lastHolder;

	public static $data;
	public static $default = ['color' => '', 'capacity' => '0', 'freezer' => '', 'food' => '', 'repairs' => '0', ];

	public static $staticData = [
		2 => ['color' => 'black', 'capacity' => '45', 'freezer' => '', 'food' => 'beef steak|milk|egg', 'repairs' => '2', ],
		4 => ['color' => 'white', 'capacity' => '20', 'freezer' => '', 'food' => 'egg|butter', 'repairs' => '0', ],
		5 => ['color' => 'silver', 'capacity' => '25', 'freezer' => '7', 'food' => '', 'repairs' => '4', ],
		8 => ['color' => 'blue', 'capacity' => '10', 'freezer' => '5', 'food' => 'jam', 'repairs' => '1', ],
	];
}
