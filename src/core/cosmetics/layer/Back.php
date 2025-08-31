<?php

namespace core\cosmetics\layer;

use core\cosmetics\CosmeticData;
use core\cosmetics\layer\LayerCosmetic;

class Back extends LayerCosmetic {

	public function getType(): int {
		return CosmeticData::TYPE_BACK;
	}

	public function getTypeName(): string {
		return "back";
	}

	public function getDataName(): string {
		return "backs/" . parent::getDataName();
	}
}
