<?php

namespace core\cosmetics\effect;

use core\AtPlayer as Player;
use core\cosmetics\Cosmetic;
use core\cosmetics\entity\CosmeticModel;

abstract class Effect extends Cosmetic {

	public int $ticks = 0;

	public function getTypeName(): string {
		return "effect";
	}

	abstract public function activate(Player|CosmeticModel $player): void;
}
