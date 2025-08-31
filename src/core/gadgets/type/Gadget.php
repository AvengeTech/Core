<?php

namespace core\gadgets\type;

use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

use core\AtPlayer as Player;
use core\lootboxes\LootBoxData;

abstract class Gadget {

	public function __construct(public Item $item) {
	}

	abstract public function getId(): int;

	abstract public function getName(): string;

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

	abstract public function getLootBoxTotal(): int;

	public function getItem(): Item {
		return $this->item;
	}

	public function getDelay(): float {
		return 1; //in seconds
	}

	public function canUse(Player $player): bool {
		return microtime(true) - $player->getSession()->getGadgets()->getLastUse($this->getName()) >= $this->getDelay();
	}

	public function onUse(Player $player): void {
		($session = $player->getSession()->getGadgets())->setLastUse($this);
		$session->takeTotal($this);

		$item = clone $this->getItem();
		$item->setCustomName($item->getCustomName() . " " . TextFormat::GRAY . "(" . number_format($session->getTotal($this)) . " left)");
		$player->getInventory()->setItemInHand($item);
	}
}
