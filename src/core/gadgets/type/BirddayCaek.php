<?php

namespace core\gadgets\type;

use pocketmine\item\VanillaItems;
use pocketmine\entity\Location;
use pocketmine\utils\TextFormat;

use core\AtPlayer as Player;
use core\gadgets\GadgetData;
use core\gadgets\entity\CakeBomb;
use core\lootboxes\LootBoxData;

class BirddayCaek extends Gadget {

	public function __construct() {
		parent::__construct(
			VanillaItems::MAGMA_CREAM()->setCustomName(TextFormat::RESET . TextFormat::AQUA . $this->getName())
		);
	}

	public function getId(): int {
		return GadgetData::BIRDDAY_CAEK;
	}

	public function getName(): string {
		return "Birdday Caek";
	}

	public function getDelay(): float {
		return 60;
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_LEGENDARY;
	}

	public function getLootBoxTotal(): int {
		return mt_rand(1, 2);
	}

	public function onUse(Player $player): void {
		parent::onUse($player);

		$dv = $player->getDirectionVector()->normalize();
		$entity = new CakeBomb(Location::fromObject($player->getPosition()->addVector($player->getDirectionVector()->multiply(1.35))->add(0, 1.5, 0), $player->getPosition()->getWorld()));
		$entity->setMotion($dv);
		$entity->spawnToAll();
	}
}
