<?php

namespace core\cosmetics\effect\trail;

use pocketmine\world\particle\SmokeParticle;

use core\AtPlayer as Player;
use core\cosmetics\CosmeticData;
use core\cosmetics\effect\trail\TrailEffect;
use core\cosmetics\entity\CosmeticModel;
use core\lootboxes\LootBoxData;

class SmokeTrail extends TrailEffect {

	public function getId(): int {
		return CosmeticData::TRAIL_SMOKE;
	}

	public function getName(): string {
		return "Smoke";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_UNCOMMON;
	}

	public function activate(Player|CosmeticModel $player): void {
		$pos = $player->getPosition();
		for ($i = 0; $i <= 4; $i++) {
			$pos->getWorld()->addParticle($pos->add(mt_rand(-8, 8) / 10, 0, mt_rand(-8, 8) / 10), new SmokeParticle(), $player->getEffectViewers());
		}
	}
}
