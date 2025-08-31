<?php

namespace core\inventory;

use core\AtPlayer;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryListener;
use pocketmine\item\Item;
use pocketmine\Server;
use skyblock\SkyBlockPlayer;

class PlayerEnderInventoryListener implements InventoryListener {

	public function __construct(protected AtPlayer $player) {
	}

	public function onSlotChange(Inventory $inventory, int $slot, Item $oldItem): void {
		$this->onContentChange($inventory, []);
	}

	public function onContentChange(Inventory $inventory, array $oldContents): void {
		if (Server::getInstance()->getTick() !== $this->player->enderUpdateTick) {
			$this->player->getEnderInv()?->pullFromPlayer();
			if ($this->player instanceof SkyBlockPlayer) $this->player->getEnderChest()->update();
		}
	}
}
