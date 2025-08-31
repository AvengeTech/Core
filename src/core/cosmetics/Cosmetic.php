<?php

namespace core\cosmetics;

use pocketmine\utils\TextFormat;

use core\lootboxes\LootBoxData;

abstract class Cosmetic {

	abstract public function getId(): int;

	abstract public function getName(): string;

	abstract public function getType(): int;

	abstract public function getTypeName(): string;

	abstract public function getRarity(): int;

	public function getRarityColor(): string {
		return match ($this->getRarity()) {
			LootBoxData::RARITY_COMMON => TextFormat::GREEN,
			LootBoxData::RARITY_UNCOMMON => TextFormat::DARK_GREEN,
			LootBoxData::RARITY_RARE => TextFormat::YELLOW,
			LootBoxData::RARITY_LEGENDARY => TextFormat::GOLD,
			LootBoxData::RARITY_DIVINE => TextFormat::RED,
		};
	}

	public function canWin(): bool {
		return true;
	}

	public function getShardWorth(): int {
		return match ($this->getRarity()) {
			LootBoxData::RARITY_COMMON => 25,
			LootBoxData::RARITY_UNCOMMON => 50,
			LootBoxData::RARITY_RARE => 100,
			LootBoxData::RARITY_LEGENDARY => 200,
			LootBoxData::RARITY_DIVINE => 300,
		};
	}
}
