<?php

namespace core\cosmetics\effect\idle;

use core\cosmetics\{
	CosmeticData
};
use core\cosmetics\effect\Effect;

abstract class IdleEffect extends Effect {

	public function getType(): int {
		return CosmeticData::TYPE_IDLE_EFFECT;
	}

	public function getTypeName(): string {
		return "idle effect";
	}
}
