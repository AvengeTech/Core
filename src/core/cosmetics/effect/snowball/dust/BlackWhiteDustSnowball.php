<?php

namespace core\cosmetics\effect\snowball\dust;

use pocketmine\color\Color;
use pocketmine\world\particle\{
	Particle,
	DustParticle
};

use core\cosmetics\CosmeticData;
use core\lootboxes\LootBoxData;

class BlackWhiteDustSnowball extends DustSnowball {

	public int $color = 0;

	public function getId(): int {
		return CosmeticData::SNOWBALL_BLACK_WHITE_DUST;
	}

	public function getName(): string {
		return "Black + White Dust";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_LEGENDARY;
	}

	public function getParticle(): Particle {
		$this->color++;
		$num = $this->color % 2 == 0 ? 0 : 255;
		return new DustParticle(new Color($num, $num, $num));
	}
}
