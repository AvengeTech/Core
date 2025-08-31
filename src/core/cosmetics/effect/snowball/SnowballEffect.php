<?php

namespace core\cosmetics\effect\snowball;

use pocketmine\entity\Entity;

use core\cosmetics\{
	CosmeticData
};
use core\cosmetics\effect\Effect;

abstract class SnowballEffect extends Effect {

	public function getType(): int {
		return CosmeticData::TYPE_SNOWBALL_EFFECT;
	}

	public function getTypeName(): string {
		return "snowball effect";
	}

	abstract public function tick(Entity $entity);
}
