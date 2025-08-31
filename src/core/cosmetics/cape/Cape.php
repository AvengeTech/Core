<?php

namespace core\cosmetics\cape;

use pocketmine\entity\Skin;
use pocketmine\scheduler\ClosureTask;

use core\{
	Core,
	AtPlayer as Player
};
use core\cosmetics\{
	Cosmetic,
	CosmeticData
};
use core\cosmetics\entity\CosmeticModel;
use core\lootboxes\LootBoxData;
use core\utils\CapeData;

class Cape extends Cosmetic {

	public function __construct(
		public int $id,
		public string $name,
		public int $rarity,
		public string $imageName,
		public bool $canWin
	) {
	}

	public function getId(): int {
		return $this->id;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getType(): int {
		return CosmeticData::TYPE_CAPE;
	}

	public function getTypeName(): string {
		return "cape";
	}

	public function getRarity(): int {
		return $this->rarity;
	}

	public function getImageName(): string {
		return $this->imageName;
	}

	public function canWin(): bool {
		return $this->canWin;
	}

	public function getShardWorth(): int {
		return match ($this->getRarity()) {
			LootBoxData::RARITY_COMMON => 500,
			LootBoxData::RARITY_UNCOMMON => 1000,
			LootBoxData::RARITY_RARE => 1500,
			LootBoxData::RARITY_LEGENDARY => 1750,
			LootBoxData::RARITY_DIVINE => 2500,
		};
	}

	public function apply(Player|CosmeticModel $player, bool $delayed = false, int $delay = 20): void {
		if (!$delayed) {
			$cd = new CapeData();
			$skin = $cd->getSkinWithCape($player, $this->getImageName());
			$player->setSkin($skin);
			$player->sendSkin();
		} else {
			Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
				if (
					($player instanceof Player && $player->isConnected()) ||
					(!$player instanceof Player && !$player->isClosed() && !$player->isFlaggedForDespawn())
				) $this->apply($player);
			}), $delay);
		}
	}
}
