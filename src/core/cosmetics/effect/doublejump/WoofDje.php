<?php

namespace core\cosmetics\effect\doublejump;


use core\AtPlayer as Player;
use core\cosmetics\CosmeticData;
use core\cosmetics\entity\CosmeticModel;
use core\lootboxes\LootBoxData;
use core\utils\PlaySound;

class WoofDje extends DoubleJumpEffect {

	public function getId(): int {
		return CosmeticData::DJ_WOOF;
	}

	public function getName(): string {
		return "Woof";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_COMMON;
	}

	public function activate(Player|CosmeticModel $player): void {
		$player->getLocation()->getWorld()->addSound($player->getPosition(), new PlaySound($player->getPosition(), "mob.wolf.bark", 2), $player->getEffectViewers());
	}
}
