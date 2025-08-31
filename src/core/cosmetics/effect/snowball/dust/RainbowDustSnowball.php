<?php

namespace core\cosmetics\effect\snowball\dust;

use pocketmine\color\Color;
use pocketmine\world\particle\{
	Particle,
	DustParticle
};

use core\cosmetics\CosmeticData;
use core\lootboxes\LootBoxData;

class RainbowDustSnowball extends DustSnowball {

	public int $color = 0;
	public array $colors = [
		[255, 0, 0],
		[255, 165, 0],
		[255, 255, 0],
		[0, 255, 0],
		[207, 246, 255],
		[255, 0, 255]
	];

	public function getId(): int {
		return CosmeticData::SNOWBALL_RAINBOW_DUST;
	}

	public function getName(): string {
		return "Rainbow Dust";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_LEGENDARY;
	}

	public function getParticle(): Particle {
		$color = $this->color++;
		if ($color >= count($this->colors)) $color = $this->color = 0;
		$color = new Color($this->colors[$color][0], $this->colors[$color][1], $this->colors[$color][2]);
		return new DustParticle($color);
	}
}
