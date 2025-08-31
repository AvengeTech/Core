<?php

namespace core\cosmetics\effect\arrow\dust;

use pocketmine\color\Color;
use pocketmine\world\particle\{
	Particle,
	DustParticle
};

use core\cosmetics\CosmeticData;
use core\lootboxes\LootBoxData;

class LightBlueDustArrow extends DustArrow {

	public function getId(): int {
		return CosmeticData::ARROW_LIGHT_BLUE_DUST;
	}

	public function getName(): string {
		return "Light Blue Dust";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_RARE;
	}

	public function getParticle(): Particle {
		return new DustParticle(new Color(207, 246, 255));
	}
}
