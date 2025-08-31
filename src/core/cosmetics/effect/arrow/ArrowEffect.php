<?php

namespace core\cosmetics\effect\arrow;

use pocketmine\entity\Entity;

use core\cosmetics\{
	CosmeticData
};
use core\cosmetics\effect\Effect;

abstract class ArrowEffect extends Effect {

	public function getType(): int {
		return CosmeticData::TYPE_ARROW_EFFECT;
	}

	public function getTypeName(): string {
		return "arrow effect";
	}

	abstract public function tick(Entity $entity);
}
