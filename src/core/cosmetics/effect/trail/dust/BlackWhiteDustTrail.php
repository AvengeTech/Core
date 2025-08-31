<?php

namespace core\cosmetics\effect\trail\dust;

use pocketmine\color\Color;
use pocketmine\world\particle\DustParticle;

use core\AtPlayer as Player;
use core\cosmetics\CosmeticData;
use core\cosmetics\effect\trail\TrailEffect;
use core\cosmetics\entity\CosmeticModel;
use core\lootboxes\LootBoxData;

class BlackWhiteDustTrail extends TrailEffect {

	public function getId(): int {
		return CosmeticData::TRAIL_BLACK_WHITE_DUST;
	}

	public function getName(): string {
		return "Black + White Dust";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_UNCOMMON;
	}

	public function activate(Player|CosmeticModel $player): void {
		$pos = $player->getPosition();
		$this->ticks++;
		$num = $this->ticks % 2 == 0 ? 0 : 255;
		$particle = new DustParticle(new Color($num, $num, $num));
		for ($i = 0; $i <= 5; $i++) {
			$pos->getWorld()->addParticle($pos->add(mt_rand(-8, 8) / 10, mt_rand(0, 15) / 10, mt_rand(-8, 8) / 10), $particle, $player->getEffectViewers());
		}
	}
}
