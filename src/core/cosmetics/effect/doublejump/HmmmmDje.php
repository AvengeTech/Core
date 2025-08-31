<?php

namespace core\cosmetics\effect\doublejump;

use pocketmine\world\particle\HappyVillagerParticle;

use core\AtPlayer as Player;
use core\cosmetics\CosmeticData;
use core\cosmetics\entity\CosmeticModel;
use core\lootboxes\LootBoxData;
use core\utils\PlaySound;

class HmmmmDje extends DoubleJumpEffect {

	public function getId(): int {
		return CosmeticData::DJ_HMMMM;
	}

	public function getName(): string {
		return "Hmmmm";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_RARE;
	}

	public function activate(Player|CosmeticModel $player): void {
		$pos = $player->getPosition();
		$player->getLocation()->getWorld()->addSound($pos, new PlaySound($pos, "mob.villager.yes", 2), $player->getEffectViewers());
		for ($i = 0; $i <= 8; $i++) {
			$pos->getWorld()->addParticle($pos->add(mt_rand(-10, 10) / 10, mt_rand(0, 15) / 10, mt_rand(-10, 10) / 10), new HappyVillagerParticle(), $player->getEffectViewers());
		}
	}
}
