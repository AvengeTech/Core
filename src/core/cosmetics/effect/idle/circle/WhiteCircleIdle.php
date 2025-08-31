<?php

namespace core\cosmetics\effect\idle\circle;

use pocketmine\color\Color;
use pocketmine\world\particle\DustParticle;

use core\AtPlayer as Player;
use core\cosmetics\CosmeticData;
use core\cosmetics\entity\CosmeticModel;
use core\lootboxes\LootBoxData;

class WhiteCircleIdle extends CircleIdleEffect {

	public function getId(): int {
		return CosmeticData::IDLE_WHITE_CIRCLE;
	}

	public function getName(): string {
		return "White Circle";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_UNCOMMON;
	}

	public function activate(Player|CosmeticModel $player): void {
		$pos = $player->getPosition();
		$particle = new DustParticle(new Color(255, 255, 255));

		for ($n = 0; $n < 10; $n++) {
			$this->ticks++;

			if ($this->ticks % 2 == 0) {
				$pos->getWorld()->addParticle($this->addCircle($pos), $particle, $player->getEffectViewers());
			}
		}
	}
}
