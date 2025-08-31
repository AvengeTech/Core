<?php

namespace core\inventory;

use core\AtPlayer;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryListener;
use pocketmine\item\Item;
use pocketmine\Server;

class PlayerInventoryListener implements InventoryListener {

	public function __construct(protected AtPlayer $player) {
	}

	public function onSlotChange(Inventory $inventory, int $slot, Item $oldItem): void {
		$this->onContentChange($inventory, []);
	}

	public function onContentChange(Inventory $inventory, array $oldContents): void {
		if (Server::getInstance()->getTick() !== $this->player->invUpdateTick) $this->player->getSeeInv()?->pullFromPlayer();
	}
}
