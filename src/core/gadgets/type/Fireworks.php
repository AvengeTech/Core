<?php

namespace core\gadgets\type;

use pocketmine\data\bedrock\item\ItemTypeNames;

use core\AtPlayer as Player;
use core\gadgets\GadgetData;
use core\gadgets\item\Firework as FireworkItem;
use core\gadgets\entity\Firework as FireworkEntity;
use core\lootboxes\LootBoxData;
use core\utils\ItemRegistry;
use core\utils\TextFormat;

class Fireworks extends Gadget {

	public function __construct() {
		parent::__construct(
			ItemRegistry::FIREWORKS()->setCustomName(TextFormat::EMOJI_CONFETTI . TextFormat::RESET . TextFormat::RED . " " . $this->getName())
		);
	}

	public function getId(): int {
		return GadgetData::FIREWORKS;
	}

	public function getName(): string {
		return "Fireworks";
	}

	public function getDelay(): float {
		return 5;
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_UNCOMMON;
	}

	public function getLootBoxTotal(): int {
		return mt_rand(1, 3);
	}

	public function onUse(Player $player): void {
		parent::onUse($player);

		$item = ItemRegistry::FIREWORKS();
		$item->addExplosion(mt_rand(0, 4), FireworkItem::getRandomColor());
		$entity = new FireworkEntity($player->getLocation(), $item);
		$entity->spawnToAll();
	}
}
