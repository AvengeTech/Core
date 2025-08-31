<?php

namespace core\cosmetics\effect\arrow\dust;

use pocketmine\color\Color;
use pocketmine\world\particle\{
	Particle,
	DustParticle
};

use core\cosmetics\CosmeticData;
use core\lootboxes\LootBoxData;

class GreenDustArrow extends DustArrow {

	public function getId(): int {
		return CosmeticData::ARROW_GREEN_DUST;
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
