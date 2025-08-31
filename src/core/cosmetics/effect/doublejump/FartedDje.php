<?php

namespace core\cosmetics\effect\doublejump;

use pocketmine\block\{
	BlockFactory,
	BlockLegacyIds,
	VanillaBlocks,
	utils\DyeColor
};
use pocketmine\color\Color;
use pocketmine\world\particle\{
	BlockBreakParticle,
	DustParticle
};

use core\AtPlayer as Player;
use core\cosmetics\CosmeticData;
use core\cosmetics\entity\CosmeticModel;
use core\lootboxes\LootBoxData;
use core\utils\PlaySound;

class FartedDje extends DoubleJumpEffect {

	public function getId(): int {
		return CosmeticData::DJ_FARTED;
	}

	public function getName(): string {
		return "Farted";
	}

	public function getRarity(): int {
		return LootBoxData::RARITY_LEGENDARY;
	}

	public function activate(Player|CosmeticModel $player): void {
		$pos = $player->getPosition();
		$player->getLocation()->getWorld()->addSound($pos, new PlaySound($pos, (mt_rand(0, 4) == 1 ? "reverb.fart.long" : "reverb.fart"), 2), $player->getEffectViewers());
		$particle = new DustParticle(new Color(150, 75, 0));
		for ($i = 0; $i <= 5; $i++) {
			$pos->getWorld()->addParticle($pos->add(mt_rand(-10, 10) / 10, mt_rand(0, 15) / 10, mt_rand(-10, 10) / 10), $particle, $player->getEffectViewers());
		}
		$pos->getWorld()->addParticle($pos, new BlockBreakParticle(VanillaBlocks::WOOL()->setColor(DyeColor::BROWN())), $player->getEffectViewers());
	}
}
