<?php

namespace core\cosmetics\effect\doublejump;

use core\cosmetics\{
	CosmeticData
};
use core\cosmetics\effect\Effect;

abstract class DoubleJumpEffect extends Effect {

	public function getType(): int {
		return CosmeticData::TYPE_DOUBLE_JUMP_EFFECT;
	}

	public function getTypeName(): string {
		return "double jump effect";
	}
}
