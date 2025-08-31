<?php

namespace core\cosmetics\effect\arrow\dust;

use pocketmine\color\Color;
use pocketmine\world\particle\{
	Particle,
	DustParticle
};

use core\cosmetics\CosmeticData;
use core\lootboxes\LootBoxData;

class WhiteDustArrow extends DustArrow {

	public function getId(): int {
		return CosmeticData::ARROW_WHITE_DUST;
	}

	public function getName(): string {
		return "White Dust";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_RARE;
	}

	public function getParticle(): Particle {
		return new DustParticle(new Color(255, 255, 255));
	}
}
