<?php

namespace core\block\inventory;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\inventory\BlockInventory;
use pocketmine\inventory\SimpleInventory;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\world\Position;
use pocketmine\world\sound\ShulkerBoxCloseSound;
use pocketmine\world\sound\ShulkerBoxOpenSound;
use pocketmine\world\sound\Sound;

class ShulkerBoxInventory extends SimpleInventory implements BlockInventory {
	use AnimatedBlockInventoryTrait;

	public function __construct(Position $holder) {
		$this->holder = $holder;
		parent::__construct(27);
	}

	protected function getOpenSound(): Sound {
		return new ShulkerBoxOpenSound();
	}

	protected function getCloseSound(): Sound {
		return new ShulkerBoxCloseSound();
	}

	public function canAddItem(Item $item): bool {
		$blockTypeId = ItemTypeIds::toBlockTypeId($item->getTypeId());
		if ($blockTypeId === BlockTypeIds::SHULKER_BOX || $blockTypeId === BlockTypeIds::DYED_SHULKER_BOX) {
			return false;
		}
		return parent::canAddItem($item);
	}

	protected function animateBlock(bool $isOpen): void {
		$holder = $this->getHolder();

		//event ID is always 1 for a chest
		$holder->getWorld()->broadcastPacketToViewers($holder, BlockEventPacket::create(BlockPosition::fromVector3($holder), 1, $isOpen ? 1 : 0));
	}
}
