<?php

namespace core\cosmetics\effect\snowball\dust;

use pocketmine\color\Color;
use pocketmine\world\particle\{
	Particle,
	DustParticle
};

use core\cosmetics\CosmeticData;
use core\lootboxes\LootBoxData;

class RedDustSnowball extends DustSnowball {

	public function getId(): int {
		return CosmeticData::SNOWBALL_RED_DUST;
	}

	public function getName(): string {
		return "Red Dust";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_UNCOMMON;
	}

	public function getParticle(): Particle {
		return new DustParticle(new Color(255, 0, 0));
	}
}
