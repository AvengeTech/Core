<?php

namespace core\staff\inventory;

use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryListener;
use pocketmine\item\Item;
use pocketmine\Server;

class SeeinvListener implements InventoryListener {

	public function __construct(protected SeeinvInventory $seeInv) {
	}

	public function onSlotChange(Inventory $inventory, int $slot, Item $oldItem): void {
		$this->onContentChange($inventory, []);
	}

	public function onContentChange(Inventory $inventory, array $oldContents): void {
		if (Server::getInstance()->getTick() !== $this->seeInv->updateTick) $this->seeInv->pushToPlayer();
	}
}
