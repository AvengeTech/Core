<?php

namespace core\cosmetics\layer;

use core\cosmetics\CosmeticData;
use core\cosmetics\layer\LayerCosmetic;

class Suit extends LayerCosmetic {

	public function getType(): int {
		return CosmeticData::TYPE_SUIT;
	}

	public function getTypeName(): string {
		return "suit";
	}

	public function getDataName(): string {
		return "suits/" . parent::getDataName();
	}
}
