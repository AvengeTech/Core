<?php

namespace core\cosmetics\effect\trail;

use core\cosmetics\{
	CosmeticData
};
use core\cosmetics\effect\Effect;

abstract class TrailEffect extends Effect {

	public function getType(): int {
		return CosmeticData::TYPE_TRAIL_EFFECT;
	}

	public function getTypeName(): string {
		return "trail effect";
	}
}
