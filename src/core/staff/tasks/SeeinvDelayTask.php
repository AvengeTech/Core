<?php

namespace core\staff\tasks;

use pocketmine\scheduler\Task;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\{
	BlockPosition,
	inventory\WindowTypes
};
use pocketmine\world\Position;

use core\AtPlayer as Player;
use core\inventory\TempInventory;

class SeeinvDelayTask extends Task {

	private $player;
	private $inventory;
	private $pos;

	public function __construct(Player $player, TempInventory $inventory, Position $pos) {
		$this->player = $player;
		$this->inventory = $inventory;
		$this->pos = $pos;
	}

	public function onRun(): void {
		$pos = $this->pos;
		if ($this->player->isConnected()) {
			$id = $this->player->getNetworkSession()->getInvManager()->getWindowId($this->inventory);
			if ($id === null) return;
			$pk = new ContainerOpenPacket();
			$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
			$pk->windowId = $id;
			$pk->windowType = WindowTypes::CONTAINER;

			$this->player->getNetworkSession()->sendDataPacket($pk);
			$this->player->getNetworkSession()->getInvManager()->syncContents($this->inventory);
		}
	}
}
