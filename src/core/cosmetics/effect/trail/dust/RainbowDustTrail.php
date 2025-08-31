<?php

namespace core\cosmetics\effect\trail\dust;

use pocketmine\color\Color;
use pocketmine\world\particle\DustParticle;

use core\AtPlayer as Player;
use core\cosmetics\CosmeticData;
use core\cosmetics\effect\trail\TrailEffect;
use core\cosmetics\entity\CosmeticModel;
use core\lootboxes\LootBoxData;

class RainbowDustTrail extends TrailEffect {

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
		return CosmeticData::TRAIL_RAINBOW_DUST;
	}

	public function getName(): string {
		return "Rainbow Dust";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_RARE;
	}

	public function activate(Player|CosmeticModel $player): void {
		$pos = $player->getPosition();
		for ($i = 0; $i <= 5; $i++) {
			$color = $this->color++;
			if ($color >= count($this->colors)) $color = $this->color = 0;
			$color = new Color($this->colors[$color][0], $this->colors[$color][1], $this->colors[$color][2]);
			$particle = new DustParticle($color);

			$pos->getWorld()->addParticle($pos->add(mt_rand(-8, 8) / 10, mt_rand(0, 15) / 10, mt_rand(-8, 8) / 10), $particle, $player->getEffectViewers());
		}
	}
}
