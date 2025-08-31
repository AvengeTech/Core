<?php

namespace core\cosmetics\effect\snowball\dust;

use pocketmine\color\Color;
use pocketmine\world\particle\{
	Particle,
	DustParticle
};

use core\cosmetics\CosmeticData;
use core\lootboxes\LootBoxData;

class GreenDustSnowball extends DustSnowball {

	public function getId(): int {
		return CosmeticData::SNOWBALL_GREEN_DUST;
	}

	public function getName(): string {
		return "Green Dust";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_UNCOMMON;
	}

	public function getParticle(): Particle {
		return new DustParticle(new Color(0, 255, 0));
	}
}
