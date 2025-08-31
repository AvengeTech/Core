<?php

namespace core\cosmetics\layer;

use core\cosmetics\CosmeticData;
use core\cosmetics\layer\LayerCosmetic;

class Shoes extends LayerCosmetic {

	public function getType(): int {
		return CosmeticData::TYPE_SHOES;
	}

	public function getTypeName(): string {
		return "shoes";
	}

	public function getDataName(): string {
		return "shoes/" . parent::getDataName();
	}
}
