<?php

namespace core\cosmetics\layer;

use core\cosmetics\CosmeticData;
use core\cosmetics\layer\LayerCosmetic;

class Hat extends LayerCosmetic {

	public function getType(): int {
		return CosmeticData::TYPE_HAT;
	}

	public function getTypeName(): string {
		return "hat";
	}

	public function getDataName(): string {
		return "hats/" . parent::getDataName();
	}
}
