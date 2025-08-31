<?php

namespace core\inbox\inventory;

use pocketmine\{
	block\tile\Nameable,
	inventory\Inventory,
	inventory\SimpleInventory,
	player\Player,
};

use pocketmine\network\mcpe\protocol\{
	UpdateBlockPacket,
	BlockActorDataPacket,

	types\CacheableNbt,
	types\BlockPosition
};

use pocketmine\block\{
	VanillaBlocks
};
use pocketmine\block\tile\{
	Tile,
	Chest
};
use pocketmine\world\Position;

use pocketmine\nbt\tag\{
	CompoundTag,
};


use core\Core;
use core\inbox\task\MessageDelayTask;
use core\inbox\object\MessageInstance;
use pocketmine\network\mcpe\convert\TypeConverter;

class MessageInventory extends SimpleInventory {

	public $nbt;

	public function __construct(public MessageInstance $message) {
		parent::__construct(54);
		$this->setContents($message->getItems());

		$this->nbt = CompoundTag::create()->setString(Tile::TAG_ID, "Chest")->setString(Nameable::TAG_CUSTOM_NAME, $this->getTitle())->setInt(Tile::TAG_X, 0)->setInt(Tile::TAG_Y, 0)->setInt(Tile::TAG_Z, 0);
	}

	public function getName(): string {
		return "MessageInventory";
	}

	public function getDefaultSize(): int {
		return 54;
	}

	public function getTitle(): string {
		return "Message Inventory";
	}

	public function doOpen(Player $player): void {
		$player->getNetworkSession()->getInvManager()->getContainerOpenCallbacks()->add(function (int $id, Inventory $inventory): array {
			return []; //trollface
		});
		$player->setCurrentWindow($this);
	}

	public function onOpen(Player $who): void {
		parent::onOpen($who);
		$pos = new Position($who->getPosition()->getFloorX(), $who->getPosition()->getFloorY() + 2, $who->getPosition()->getFloorZ(), $who->getWorld());

		$this->nbt->setInt(Tile::TAG_X, $pos->x);
		$this->nbt->setInt(Tile::TAG_Y, $pos->y);
		$this->nbt->setInt(Tile::TAG_Z, $pos->z);

		$this->nbt->setInt(Chest::TAG_PAIRX, $pos->x + 1);
		$this->nbt->setInt(Chest::TAG_PAIRZ, $pos->z);

		$pk = new UpdateBlockPacket();
		$pk->blockRuntimeId = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::CHEST()->getStateId());
		$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
		$who->getNetworkSession()->sendDataPacket($pk);
		$pk = new UpdateBlockPacket();
		$pk->blockRuntimeId = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::CHEST()->getStateId());
		$pk->blockPosition = new BlockPosition($pos->x + 1, $pos->y, $pos->z);
		$who->getNetworkSession()->sendDataPacket($pk);

		$pk = new BlockActorDataPacket();
		$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
		$pk->nbt = new CacheableNbt($this->nbt);
		$who->getNetworkSession()->sendDataPacket($pk);

		Core::getInstance()->getScheduler()->scheduleDelayedTask(new MessageDelayTask($who, $this, $pos), 4);
	}

	public function onClose(Player $who): void {
		if ($this->message->verify($source)) {
			$source->setItems($this->getContents());
			$source->inventory = null;
		}
		parent::onClose($who);

		$pos = new Position($this->nbt->getInt(Tile::TAG_X), $this->nbt->getInt(Tile::TAG_Y), $this->nbt->getInt(Tile::TAG_Z), $who->getWorld());

		$this->nbt->setInt(Tile::TAG_X, 0);
		$this->nbt->setInt(Tile::TAG_Y, 0);
		$this->nbt->setInt(Tile::TAG_Z, 0);

		$pk = new UpdateBlockPacket();
		$pk->blockRuntimeId = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($who->getWorld()->getBlock($pos)->getStateId());
		$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
		$who->getNetworkSession()->sendDataPacket($pk);

		$pk = new UpdateBlockPacket();
		$pk->blockRuntimeId = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($who->getWorld()->getBlock($pos->add(1, 0, 0)->floor())->getStateId());
		$pk->blockPosition = new BlockPosition($pos->x + 1, $pos->y, $pos->z);
		$who->getNetworkSession()->sendDataPacket($pk);

		//no container close or removeCurrentWindow()!!

	}
}
