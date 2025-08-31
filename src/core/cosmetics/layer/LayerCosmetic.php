<?php

namespace core\cosmetics\layer;

use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\scheduler\ClosureTask;

use core\AtPlayer as Player;
use core\Core;
use core\cosmetics\Cosmetic;
use core\lootboxes\LootBoxData;

abstract class LayerCosmetic extends Cosmetic {

	public function __construct(
		public int $id,
		public string $name,
		public int $rarity,
		public string $dataName,
		public ?string $animation,
		public bool $canWin
	) {
		$this->animation ??= "";
	}

	public function getId(): int {
		return $this->id;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getRarity(): int {
		return $this->rarity;
	}

	public function getDataName(): string {
		return $this->dataName;
	}

	public function hasAnimation(): bool {
		return $this->animation !== "";
	}

	public function getAnimation(): string {
		return $this->animation;
	}

	public function sendAnimation(Player $player, int $ofId, bool $delayed = true, int $delay = 30): void {
		$func = function () use ($player, $ofId): void {
			if ($player->isConnected()) {
				$anim = $this->getAnimation();
				$packet = AnimateEntityPacket::create($anim, "", "", 0, "", 0, [$ofId]);
				$player->getNetworkSession()->sendDataPacket($packet);
			}
		};

		if ($delayed) {
			Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask($func), $delay);
		} else {
			$func();
		}
	}

	public function canWin(): bool {
		return $this->canWin;
	}

	public function getShardWorth(): int {
		return match ($this->getRarity()) {
			LootBoxData::RARITY_COMMON => 75,
			LootBoxData::RARITY_UNCOMMON => 150,
			LootBoxData::RARITY_RARE => 200,
			LootBoxData::RARITY_LEGENDARY => 300,
			LootBoxData::RARITY_DIVINE => 500,
		};
	}
}
