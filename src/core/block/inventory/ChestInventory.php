<?php

namespace core\block\inventory;

use pocketmine\block\inventory\BlockInventory;
use pocketmine\inventory\SimpleInventory;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\world\Position;
use pocketmine\world\sound\ChestCloseSound;
use pocketmine\world\sound\ChestOpenSound;
use pocketmine\world\sound\Sound;

class ChestInventory extends SimpleInventory implements BlockInventory {
	use AnimatedBlockInventoryTrait;

	public function __construct(Position $holder) {
		$this->holder = $holder;
		parent::__construct(27);
	}

	protected function getOpenSound(): Sound {
		return new ChestOpenSound();
	}

	protected function getCloseSound(): Sound {
		return new ChestCloseSound();
	}

	public function animateBlock(bool $isOpen): void {
		$holder = $this->getHolder();

		//event ID is always 1 for a chest
		$holder->getWorld()->broadcastPacketToViewers($holder, BlockEventPacket::create(BlockPosition::fromVector3($holder), 1, $isOpen ? 1 : 0));
	}
}