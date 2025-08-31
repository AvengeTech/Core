<?php

namespace core\cosmetics\effect\arrow\dust;

use pocketmine\color\Color;
use pocketmine\world\particle\{
	Particle,
	DustParticle
};

use core\cosmetics\CosmeticData;
use core\lootboxes\LootBoxData;

class BlackDustArrow extends DustArrow {

	public function getId(): int {
		return CosmeticData::ARROW_BLACK_DUST;
	}

	public function getName(): string {
		return "Black Dust";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_RARE;
	}

	public function getParticle(): Particle {
		return new DustParticle(new Color(0, 0, 0));
	}
}
