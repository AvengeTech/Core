<?php

namespace core\cosmetics\effect\snowball\dust;

use pocketmine\color\Color;
use pocketmine\world\particle\{
	Particle,
	DustParticle
};

use core\cosmetics\CosmeticData;
use core\lootboxes\LootBoxData;

class LightPurpleDustSnowball extends DustSnowball {

	public function getId(): int {
		return CosmeticData::SNOWBALL_LIGHT_PURPLE_DUST;
	}

	public function getName(): string {
		return "Light Purple Dust";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_RARE;
	}

	public function getParticle(): Particle {
		return new DustParticle(new Color(255, 0, 255));
	}
}
