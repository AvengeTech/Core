<?php

namespace core\cosmetics\effect\idle\circle;

use pocketmine\color\Color;
use pocketmine\world\particle\DustParticle;

use core\AtPlayer as Player;
use core\cosmetics\CosmeticData;
use core\cosmetics\entity\CosmeticModel;
use core\lootboxes\LootBoxData;

class BlackWhiteCircleIdle extends CircleIdleEffect {

	public int $color = 0;

	public function getId(): int {
		return CosmeticData::IDLE_BLACK_WHITE_CIRCLE;
	}

	public function getName(): string {
		return "Black + White Circle";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_RARE;
	}

	public function activate(Player|CosmeticModel $player): void {
		$pos = $player->getPosition();

		$this->color++;
		$num = $this->color % 2 == 0 ? 0 : 255;
		$particle = new DustParticle(new Color($num, $num, $num));

		for ($n = 0; $n < 10; $n++) {
			$this->ticks++;

			if ($this->ticks % 2 == 0) {
				$pos->getWorld()->addParticle($this->addCircle($pos), $particle, $player->getEffectViewers());
			}
		}
	}
}
