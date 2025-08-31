<?php

namespace core\staff\inventory;

use core\AtPlayer;
use pocketmine\{
	block\tile\Chest,
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

use pocketmine\item\Item;
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
use pocketmine\item\VanillaItems;
use prison\PrisonSession;
use skyblock\SkyBlockSession;

class SeeinvInventory extends SimpleInventory implements TempInventory {

	const SLOT_HELMET = 45;
	const SLOT_CHESTPLATE = 46;
	const SLOT_LEGGINGS = 47;
	const SLOT_BOOTS = 48;

	const SLOT_HAND = 53;

	public int $updateTick = -1;
	public $nbt;

	public function __construct(public PlayerSession $session) {
		parent::__construct(54);
		$this->getListeners()->clear();
		$this->getListeners()->add(new SeeinvListener($this));

		$this->pullFromPlayer();
		$this->nbt = CompoundTag::create()->setString(Tile::TAG_ID, "Chest")->setString(Nameable::TAG_CUSTOM_NAME, $this->getTitle())->setInt(Tile::TAG_X, 0)->setInt(Tile::TAG_Y, 0)->setInt(Tile::TAG_Z, 0);
	}

	public function getCombinedInventories(): array {
		$player = $this->session->getPlayer();
		if ($player instanceof AtPlayer && $player->isLoaded() && $player->isConnected()) {
			$inv = $player->getInventory();
			$armor = $player->getArmorInventory();
			$hand = $player->getCursorInventory();

			$items = [];
			foreach ($inv->getContents() as $slot => $item) {
				$items[$slot] = $item;
			}

			$items[self::SLOT_HELMET] = $armor->getHelmet();
			$items[self::SLOT_CHESTPLATE] = $armor->getChestplate();
			$items[self::SLOT_LEGGINGS] = $armor->getLeggings();
			$items[self::SLOT_BOOTS] = $armor->getBoots();

			$items[self::SLOT_HAND] = $hand->getItem(0);

			return $items;
		} elseif ($this->session instanceof SkyBlockSession && $this->session->getData()->isLoaded()) {
			$inv = $this->session->getData()->inventory;
			$armor = $this->session->getData()->armorinventory;
			$hand = [VanillaItems::AIR()];

			$items = [];
			foreach ($inv as $slot => $item) {
				$items[$slot] = $item;
			}

			$items[self::SLOT_HELMET] = isset($armor[0]) ? $armor[0] : VanillaItems::AIR();
			$items[self::SLOT_CHESTPLATE] = isset($armor[1]) ? $armor[1] : VanillaItems::AIR();
			$items[self::SLOT_LEGGINGS] = isset($armor[2]) ? $armor[2] : VanillaItems::AIR();
			$items[self::SLOT_BOOTS] = isset($armor[3]) ? $armor[3] : VanillaItems::AIR();

			$items[self::SLOT_HAND] = $hand[0];

			return $items;
		} elseif ($this->session instanceof PrisonSession && $this->session->getData()->isLoaded()) {
			$inv = $this->session->getData()->inventory;
			$armor = $this->session->getData()->armorinventory;
			$hand = [VanillaItems::AIR()];

			$items = [];
			foreach ($inv as $slot => $item) {
				$items[$slot] = $item;
			}

			$items[self::SLOT_HELMET] = isset($armor[0]) ? $armor[0] : VanillaItems::AIR();
			$items[self::SLOT_CHESTPLATE] = isset($armor[1]) ? $armor[1] : VanillaItems::AIR();
			$items[self::SLOT_LEGGINGS] = isset($armor[2]) ? $armor[2] : VanillaItems::AIR();
			$items[self::SLOT_BOOTS] = isset($armor[3]) ? $armor[3] : VanillaItems::AIR();

			$items[self::SLOT_HAND] = $hand[0];

			return $items;
		}
		return [];
	}

	public function isValidSlot(int $slot): bool {
		return (($slot >= 0 && $slot <= 26) || in_array($slot, [self::SLOT_BOOTS, self::SLOT_CHESTPLATE, self::SLOT_HAND, self::SLOT_HELMET, self::SLOT_LEGGINGS])) && ($this->session instanceof SkyBlockSession ? !$this->session->getData()->isLoading() : true);
	}

	public function pullFromPlayer() {
		$this->updateTick = Server::getInstance()->getTick();
		$this->setContents($this->getCombinedInventories());
	}

	public function pushToPlayer() {
		$inv = $this->getPart(0);
		$armor = $this->getPart(1);
		$cursor = $this->getPart(2);
		$this->session->updateInventory($inv, $armor, $cursor);
	}

	/** @return Item[] */
	public function getPart(int $part = 0, bool $includeEmpty = false): array {
		$rtn = [];
		switch ($part) {
			case 0: {
					$i = $this->getContents(true);
					$rtn = array_slice($i, 0, 27, true);
					break;
				}
			case 1: {
					$i = [$this->getItem(self::SLOT_HELMET), $this->getItem(self::SLOT_CHESTPLATE), $this->getItem(self::SLOT_LEGGINGS), $this->getItem(self::SLOT_BOOTS)];
					$rtn = $i;
					break;
				}
			case 2: {
					$i = [$this->getItem(self::SLOT_HAND)];
					$rtn = $i;
					break;
				}
		}
		if (!$includeEmpty) $rtn = array_filter($rtn, function (Item $itm): bool {
			return !$itm->isNull() && !$itm->equals(VanillaItems::AIR(), false, false);
		});
		return $rtn;
	}

	public function getName(): string {
		return "SeeinvInventory";
	}

	public function getDefaultSize(): int {
		return 54;
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

		$this->nbt->setInt(Chest::TAG_PAIRX, $pos->x + 1);
		$this->nbt->setInt(Chest::TAG_PAIRZ, $pos->z);

		$pk = new UpdateBlockPacket();
		$pk->blockPosition = new BlockPosition($pos->x, $pos->y, $pos->z);
		$pk->blockRuntimeId = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::CHEST()->getStateId());
		$who->getNetworkSession()->sendDataPacket($pk);
		$pk = new UpdateBlockPacket();
		$pk->blockPosition = new BlockPosition($pos->x + 1, $pos->y, $pos->z);
		$pk->blockRuntimeId = TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::CHEST()->getStateId());
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

		if (is_null($this->session->getPlayer())) $this->pushToPlayer();
		//$who->removeCurrentWindow();
	}

	public function closeToAll(bool $else = false): void {
		foreach ($this->getViewers() as $v) {
			$v->sendMessage(TextFormat::RN . TextFormat::YELLOW . $this->session->getGamertag() . TextFormat::GRAY . ($else ? " was detected elsewhere!" : " disconnected!") . " Inventory was closed for data integrity.");
			$this->onClose($v);
		}
	}
}
