<?php

namespace core\inbox\task;

use pocketmine\scheduler\Task;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\{
	BlockPosition,
	inventory\WindowTypes
};
use pocketmine\world\Position;

use core\AtPlayer as Player;
use core\inbox\inventory\MessageInventory;


class MessageDelayTask extends Task {

	public function __construct(
		private Player $player,
		private MessageInventory $inventory,
		private Position $pos
	) {
	}

	public function onRun(): void {
		$pos = $this->pos;
		if ($this->player->isConnected()) {
			$pk = new ContainerOpenPacket();
			$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
			$pk->windowId = (int) $this->player->getNetworkSession()->getInvManager()->getWindowId($this->inventory);
			$pk->windowType = WindowTypes::CONTAINER;

			$this->player->getNetworkSession()->sendDataPacket($pk);
			$this->player->getNetworkSession()->getInvManager()->syncContents($this->inventory);
		}
	}
}
