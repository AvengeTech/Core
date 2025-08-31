<?php

namespace core\cosmetics\effect\arrow\dust;

use pocketmine\color\Color;
use pocketmine\world\particle\{
	Particle,
	DustParticle
};

use core\cosmetics\CosmeticData;
use core\lootboxes\LootBoxData;

class YellowDustArrow extends DustArrow {

	public function getId(): int {
		return CosmeticData::ARROW_YELLOW_DUST;
	}

	public function getName(): string {
		return "Yellow Dust";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_UNCOMMON;
	}

	public function getParticle(): Particle {
		return new DustParticle(new Color(255, 255, 0));
	}
}
