<?php

namespace core\utils;

use pocketmine\block\tile\TileFactory;

use core\block\tile\Chest;
use core\block\tile\EnderChest;
use core\block\tile\ShulkerBox;
use faction\block\tile\BreakableBlockTile;

class TileRegistry {

	public static function setup(string $serverType): void {
		TileFactory::getInstance()->register(Chest::class, ["Chest", "minecraft:chest"]);
		TileFactory::getInstance()->register(ShulkerBox::class, ["ShulkerBox", "minecraft:shulker_box"]);
		TileFactory::getInstance()->register(EnderChest::class, ["EnderChest", "minecraft:ender_chest"]);

		switch($serverType){
			case "faction":
				TileFactory::getInstance()->register(BreakableBlockTile::class, ["BreakableBlock", "avengetech:breakable_block"]);
				break;
		}
	}
}
