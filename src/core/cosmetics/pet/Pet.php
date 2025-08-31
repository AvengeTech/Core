<?php

namespace core\cosmetics\pet;

use core\cosmetics\{
	Cosmetic,
	CosmeticData
};

abstract class Pet extends Cosmetic {

	public function getType(): int {
		return CosmeticData::TYPE_PET;
	}
}
