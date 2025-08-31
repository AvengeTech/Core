<?php

namespace core\cosmetics\effect\idle\circle;

use pocketmine\color\Color;
use pocketmine\world\particle\DustParticle;

use core\AtPlayer as Player;
use core\cosmetics\CosmeticData;
use core\cosmetics\entity\CosmeticModel;
use core\lootboxes\LootBoxData;

class RainbowCircleIdle extends CircleIdleEffect {

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
		return CosmeticData::IDLE_RAINBOW_CIRCLE;
	}

	public function getName(): string {
		return "Rainbow Circle";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_RARE;
	}

	public function activate(Player|CosmeticModel $player): void {
		$pos = $player->getPosition();

		$color = $this->color++;
		if ($color >= count($this->colors)) $color = $this->color = 0;
		$color = new Color($this->colors[$color][0], $this->colors[$color][1], $this->colors[$color][2]);
		$particle = new DustParticle($color);

		for ($n = 0; $n < 10; $n++) {
			$this->ticks++;

			if ($this->ticks % 2 == 0) {
				$pos->getWorld()->addParticle($this->addCircle($pos), $particle, $player->getEffectViewers());
			}
		}
	}
}
