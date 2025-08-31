<?php

namespace core\gadgets\type;

use pocketmine\item\VanillaItems;
use pocketmine\entity\Location;
use pocketmine\utils\TextFormat;

use core\AtPlayer as Player;
use core\gadgets\GadgetData;
use core\gadgets\entity\TntEntity;
use core\lootboxes\LootBoxData;

class TntCannon extends Gadget {

	public function __construct() {
		parent::__construct(
			VanillaItems::GUN_POWDER()->setCustomName(TextFormat::RESET . TextFormat::RED . $this->getName())
		);
	}

	public function getId(): int {
		return GadgetData::TNT_CANNON;
	}

	public function getName(): string {
		return "TNT Cannon";
	}

	public function getDelay(): float {
		return 2;
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_RARE;
	}

	public function getLootBoxTotal(): int {
		return mt_rand(10, 30);
	}

	public function onUse(Player $player): void {
		parent::onUse($player);

		foreach (array_merge([$player], $player->getViewers()) as $viewer) {
			$viewer->playSound("firework.launch", $player->getPosition(), 50);
		}
		$dv = $player->getDirectionVector()->normalize()->multiply(1.3);
		$entity = new TntEntity(Location::fromObject($player->getPosition()->addVector($player->getDirectionVector()->multiply(1.35))->add(0, 1.5, 0), $player->getPosition()->getWorld()));
		$entity->setMotion($dv);
		$entity->spawnToAll();
	}
}
