<?php

namespace core\staff\inventory;

use core\AtPlayer;
use pocketmine\{
	block\VanillaBlocks,
	inventory\Inventory,
	inventory\SimpleInventory,
	player\Player,
	Server,
	world\Position
};

use pocketmine\network\mcpe\protocol\{
	BlockActorDataPacket,
	UpdateBlockPacket,

	types\CacheableNbt,
	types\BlockPosition
};

use pocketmine\block\tile\Tile;
use pocketmine\nbt\tag\{
	CompoundTag,
};
use pocketmine\block\tile\Nameable;
use pocketmine\network\mcpe\convert\TypeConverter;

use core\Core;
use core\inventory\TempInventory;
use core\session\PlayerSession;
use core\staff\tasks\SeeinvDelayTask;
use core\utils\TextFormat;
use skyblock\SkyBlockPlayer;
use skyblock\SkyBlockSession;

class EnderinvInventory extends SimpleInventory implements TempInventory {

	public int $updateTick = -1;
	public $nbt;

	public function __construct(public PlayerSession $session) {
		parent::__construct(27);
		$this->getListeners()->clear();
		$this->getListeners()->add(new EnderinvListener($this));

		$this->pullFromPlayer();
		$this->nbt = CompoundTag::create()->setString(Tile::TAG_ID, "Chest")->setString(Nameable::TAG_CUSTOM_NAME, $this->getTitle())->setInt(Tile::TAG_X, 0)->setInt(Tile::TAG_Y, 0)->setInt(Tile::TAG_Z, 0);
	}

	public function getCombinedInventories(): array {
		$player = $this->session->getPlayer();
		if ($player instanceof AtPlayer && $player->isLoaded() && $player->isConnected()) {
			$inv = $player->getEnderInventory();

			$items = [];
			foreach ($inv->getContents() as $slot => $item) {
				$items[$slot] = $item;
			}

			return $items;
		} elseif ($this->session instanceof SkyBlockSession && $this->session->getData()->isLoaded()) {
			$inv = $this->session->getData()->enderchest_inventory;

			$items = [];
			foreach ($inv as $slot => $item) {
				$items[$slot] = $item;
			}

			return $items;
		}
		return [];
	}

	public function isValidSlot(int $slot): bool {
		return ($this->session instanceof SkyBlockSession ? !$this->session->getData()->isLoading() : true);
	}

	public function pullFromPlayer() {
		$this->updateTick = Server::getInstance()->getTick();
		$this->setContents($this->getCombinedInventories());
	}

	public function pushToPlayer() {
		$this->session->updateEnderInventory($this->getContents());
		$player = $this->session->getPlayer();
		if ($player instanceof SkyBlockPlayer) {
			$player->getEnderChest()->update();
		}
	}

	public function getName(): string {
		return "EnderinvInventory";
	}

	public function getDefaultSize(): int {
		return 27;
	}

	public function getSize(): int {
		return 27;
	}

	public function getTitle(): string {
		return $this->session->getGamertag() . "'s Inventory";
	}

	public function getPlayer(): ?AtPlayer {
		return $this->session->getPlayer();
	}

	public function doOpen(Player $player): bool {
		$player->getNetworkSession()->getInvManager()->getContainerOpenCallbacks()->add(function (int $id, Inventory $inventory): array {
			return []; //trollface
		});
		return $player->setCurrentWindow($this);
	}

	public function onOpen(Player $who): void {
		parent::onOpen($who);
		$vec = $who->getPosition()->addVector($who->getDirectionVector()->multiply(-3.5))->round();
		$pos = new Position($vec->x, $vec->y, $vec->z, $who->getWorld());

		$this->nbt->setInt(Tile::TAG_X, $pos->x);
		$this->nbt->setInt(Tile::TAG_Y, $pos->y);
		$this->nbt->setInt(Tile::TAG_Z, $pos->z);

		$pk = new UpdateBlockPacket();
		$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
		$pk->blockRuntimeId = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::ENDER_CHEST()->getStateId());
		$who->getNetworkSession()->sendDataPacket($pk);

		$pk = new BlockActorDataPacket();
		$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
		$pk->nbt = new CacheableNbt($this->nbt);
		$who->getNetworkSession()->sendDataPacket($pk);

		Core::getInstance()->getScheduler()->scheduleDelayedTask(new SeeinvDelayTask($who, $this, $pos), 4);
	}

	public function onClose(Player $who): void {
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

		//$who->removeCurrentWindow();
	}

	public function closeToAll(bool $else = false): void {
		foreach ($this->getViewers() as $v) {
			$v->sendMessage(TextFormat::RN . TextFormat::YELLOW . $this->session->getGamertag() . TextFormat::GRAY . ($else ? " was detected elsewhere!" : " disconnected!") . " Inventory was closed for data integrity.");
			$this->onClose($v);
		}
	}
}
