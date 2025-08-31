<?php

namespace core\cosmetics\effect\snowball\dust;

use pocketmine\color\Color;
use pocketmine\world\particle\{
	Particle,
	DustParticle
};

use core\cosmetics\CosmeticData;
use core\lootboxes\LootBoxData;

class OrangeDustSnowball extends DustSnowball {

	public function getId(): int {
		return CosmeticData::SNOWBALL_ORANGE_DUST;
	}

	public function getName(): string {
		return "Orange Dust";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_UNCOMMON;
	}

	public function getParticle(): Particle {
		return new DustParticle(new Color(255, 165, 0));
	}
}
