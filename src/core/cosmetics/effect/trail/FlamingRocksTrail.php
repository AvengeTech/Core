<?php

namespace core\cosmetics\effect\trail;

use pocketmine\world\particle\{
	LavaParticle,
	SmokeParticle
};

use core\AtPlayer as Player;
use core\cosmetics\CosmeticData;
use core\cosmetics\effect\trail\TrailEffect;
use core\cosmetics\entity\CosmeticModel;
use core\lootboxes\LootBoxData;

class FlamingRocksTrail extends TrailEffect {

	public function getId(): int {
		return CosmeticData::TRAIL_FLAMING_ROCKS;
	}

	public function getName(): string {
		return "Flaming Rocks";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_UNCOMMON;
	}

	public function activate(Player|CosmeticModel $player): void {
		$pos = $player->getPosition();
		$newPos = $pos->add(mt_rand(-8, 8) / 10, 0, mt_rand(-8, 8) / 10);
		$pos->getWorld()->addParticle($newPos, new LavaParticle(), $player->getEffectViewers());
		$pos->getWorld()->addParticle($newPos, new SmokeParticle(), $player->getEffectViewers());
	}
}
