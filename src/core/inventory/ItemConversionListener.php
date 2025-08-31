<?php

namespace core\inventory;

use core\AtPlayer;
use core\items\Elytra;
use core\items\type\Armor;
use core\items\type\{
	TieredTool,
	Axe,
	Hoe,
	Pickaxe,
	Shovel,
	Sword
};
use core\utils\ItemRegistry;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryListener;
use pocketmine\item\Armor as PMArmor;
use pocketmine\item\Item;
use pocketmine\item\TieredTool as PMTieredTool;
use pocketmine\item\TurtleHelmet;

class ItemConversionListener implements InventoryListener {

	public function __construct(protected AtPlayer $player) {
	}

	public function onSlotChange(Inventory $inventory, int $slot, Item $oldItem): void {
		$item = $inventory->getItem($slot);
		ItemRegistry::fixFuckedItem($item);
		if ($item instanceof PMTieredTool && !($item instanceof TieredTool || $item instanceof Axe || $item instanceof Hoe || $item instanceof Pickaxe || $item instanceof Shovel || $item instanceof Sword)) {
			$fixedItem = ItemRegistry::convertToETool($item);
			$inventory->setItem($slot, $fixedItem);
		}
		if ($item instanceof PMArmor && !($item instanceof Armor || $item instanceof Elytra || $item instanceof TurtleHelmet)) {
			$fixedItem = ItemRegistry::convertToEArmor($item);
			$inventory->setItem($slot, $fixedItem);
		}
	}

	public function onContentChange(Inventory $inventory, array $oldContents): void {
		foreach ($inventory->getContents() as $slot => $item) {
			if ($item instanceof PMTieredTool && !($item instanceof TieredTool || $item instanceof Axe || $item instanceof Hoe || $item instanceof Pickaxe || $item instanceof Shovel || $item instanceof Sword)) {
				$fixedItem = ItemRegistry::convertToETool($item);
				$inventory->setItem($slot, $fixedItem);
			}
			if ($item instanceof PMArmor && !($item instanceof Armor)) {
				$fixedItem = ItemRegistry::convertToEArmor($item);
				$inventory->setItem($slot, $fixedItem);
			}
		}
	}
}
