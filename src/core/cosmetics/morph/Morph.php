<?php

namespace core\cosmetics\morph;

use core\cosmetics\{
	Cosmetic,
	CosmeticData
};

abstract class Morph extends Cosmetic {

	public function getType(): int {
		return CosmeticData::TYPE_MORPH;
	}
}
