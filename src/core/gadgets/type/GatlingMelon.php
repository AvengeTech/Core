<?php

namespace core\gadgets\type;

use pocketmine\item\VanillaItems;
use pocketmine\entity\Location;
use pocketmine\utils\TextFormat;

use core\AtPlayer as Player;
use core\gadgets\GadgetData;
use core\gadgets\entity\CollisionItem;
use core\lootboxes\LootBoxData;

class GatlingMelon extends Gadget {

	public function __construct() {
		parent::__construct(
			VanillaItems::GLISTERING_MELON()->setCustomName(TextFormat::RESET . TextFormat::YELLOW . $this->getName())
		);
	}

	public function getId(): int {
		return GadgetData::GATLING_MELON;
	}

	public function getName(): string {
		return "Gatling Melon";
	}

	public function getDelay(): float {
		return 0.2;
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_COMMON;
	}

	public function getLootBoxTotal(): int {
		return mt_rand(15, 30);
	}

	public function onUse(Player $player): void {
		parent::onUse($player);

		foreach (array_merge([$player], $player->getViewers()) as $viewer) {
			$viewer->playSound("gunshot", $player->getPosition(), 50);
		}
		$dv = $player->getDirectionVector()->normalize()->multiply(2);
		$entity = new CollisionItem(Location::fromObject($player->getPosition()->addVector($player->getDirectionVector()->multiply(1.35))->add(0, 1.5, 0), $player->getPosition()->getWorld()), VanillaItems::MELON(), function (Player $player, CollisionItem $item) use ($dv): void {
			if ($item->getPosition()->getWorld()->getBlock($item->getPosition()->subtract(0, 0.2, 0))->getTypeId() == 0) {
				if (!($bs = $player->getBottomStacked())->getGameSession()->getParkour()->hasCourseAttempt()) $bs->setMotion($dv->multiply(0.5));
			}
		});
		$entity->setMotion($dv);
		$entity->setDespawnDelay(100);
		$entity->spawnToAll();
	}
}
