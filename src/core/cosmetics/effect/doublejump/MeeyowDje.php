<?php

namespace core\cosmetics\effect\doublejump;


use core\AtPlayer as Player;
use core\cosmetics\CosmeticData;
use core\cosmetics\entity\CosmeticModel;
use core\lootboxes\LootBoxData;
use core\utils\PlaySound;

class MeeyowDje extends DoubleJumpEffect {

	public function getId(): int {
		return CosmeticData::DJ_MEEYOW;
	}

	public function getName(): string {
		return "Meeyow";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_COMMON;
	}

	public function activate(Player|CosmeticModel $player): void {
		$player->getLocation()->getWorld()->addSound($player->getPosition(), new PlaySound($player->getPosition(), mt_rand(1, 5) === 1 ? "mob.cat.purreow" : "mob.cat.meow", 2), $player->getEffectViewers());
	}
}
