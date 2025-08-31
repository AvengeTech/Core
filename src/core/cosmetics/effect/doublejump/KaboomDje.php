<?php

namespace core\cosmetics\effect\doublejump;

use pocketmine\world\particle\HugeExplodeSeedParticle;
use pocketmine\world\sound\ExplodeSound;

use core\AtPlayer as Player;
use core\cosmetics\CosmeticData;
use core\cosmetics\entity\CosmeticModel;
use core\lootboxes\LootBoxData;

class KaboomDje extends DoubleJumpEffect {

	public function getId(): int {
		return CosmeticData::DJ_KABOOM;
	}

	public function getName(): string {
		return "Kaboom";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_UNCOMMON;
	}

	public function activate(Player|CosmeticModel $player): void {
		$player->getLocation()->getWorld()->addSound($player->getPosition(), new ExplodeSound(), $player->getEffectViewers());
		$player->getLocation()->getWorld()->addParticle($player->getPosition(), new HugeExplodeSeedParticle(), $player->getEffectViewers());
	}
}
