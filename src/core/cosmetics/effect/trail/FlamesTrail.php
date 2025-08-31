<?php

namespace core\cosmetics\effect\trail;

use pocketmine\world\particle\{
	EntityFlameParticle,
	FlameParticle
};

use core\AtPlayer as Player;
use core\cosmetics\CosmeticData;
use core\cosmetics\effect\trail\TrailEffect;
use core\cosmetics\entity\CosmeticModel;
use core\lootboxes\LootBoxData;

class FlamesTrail extends TrailEffect {

	public function getId(): int {
		return CosmeticData::TRAIL_FLAMES;
	}

	public function getName(): string {
		return "Flames";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_UNCOMMON;
	}

	public function activate(Player|CosmeticModel $player): void {
		$pos = $player->getPosition();
		$this->ticks++;
		if ($this->ticks % 3 == 0) {
			$pos->getWorld()->addParticle($pos->add(mt_rand(-8, 8) / 10, 0, mt_rand(-8, 8) / 10), new EntityFlameParticle(), $player->getEffectViewers());
			$pos->getWorld()->addParticle($pos->add(mt_rand(-8, 8) / 10, 0, mt_rand(-8, 8) / 10), new EntityFlameParticle(), $player->getEffectViewers());
		} else {
			$pos->getWorld()->addParticle($pos->add(mt_rand(-8, 8) / 10, 0, mt_rand(-8, 8) / 10), new FlameParticle(), $player->getEffectViewers());
			$pos->getWorld()->addParticle($pos->add(mt_rand(-8, 8) / 10, 0, mt_rand(-8, 8) / 10), new FlameParticle(), $player->getEffectViewers());
		}
	}
}
