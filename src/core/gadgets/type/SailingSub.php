<?php

namespace core\gadgets\type;

use pocketmine\item\VanillaItems;
use pocketmine\entity\Location;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\GhastShootSound;

use core\AtPlayer as Player;
use core\gadgets\GadgetData;
use core\gadgets\entity\SailingSub as SS;
use core\lootboxes\LootBoxData;

class SailingSub extends Gadget {

	public function __construct() {
		parent::__construct(
			VanillaItems::BREAD()->setCustomName(TextFormat::RESET . TextFormat::GOLD . $this->getName())
		);
	}

	public function getId(): int {
		return GadgetData::SAILING_SUB;
	}

	public function getName(): string {
		return "Sailing Sub";
	}

	public function getDelay(): float {
		return 10;
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_UNCOMMON;
	}

	public function getLootBoxTotal(): int {
		return mt_rand(2, 5);
	}

	public function onUse(Player $player): void {
		parent::onUse($player);

		$player->getPosition()->getWorld()->addSound($player->getPosition(), new GhastShootSound());
		$dv = $player->getDirectionVector()->normalize()->multiply(2);
		$entity = new SS(Location::fromObject($player->getPosition()->addVector($player->getDirectionVector()->multiply(1.35))->add(0, 1.5, 0), $player->getPosition()->getWorld()));
		$entity->setMotion($dv);
		$entity->setRotation($player->getLocation()->yaw - 180, 0);
		$entity->spawnToAll();

		$entity->sitDown($player);
	}
}
