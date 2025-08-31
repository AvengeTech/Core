<?php

namespace core\gadgets;

use pocketmine\block\VanillaBlocks;

use core\Core;
use core\gadgets\type\Gadget;
use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\MySqlQuery;
use core\utils\TextFormat;

class GadgetsComponent extends SaveableComponent {

	public array $gadgets = [];
	public int $defaultGadget = -1;

	public array $uses = [];

	public function getName(): string {
		return "gadgets";
	}

	public function getGadgets(): array {
		return $this->gadgets;
	}

	public function getTotal(Gadget|int $gadget): int {
		return $this->gadgets[$gadget instanceof Gadget ? $gadget->getId() : $gadget] ?? 0;
	}

	public function setTotal(Gadget|int $gadget, int $total): void {
		$this->gadgets[$gadget instanceof Gadget ? $gadget->getId() : $gadget] = $total;
		$this->setChanged();
	}

	public function addTotal(Gadget|int $gadget, int $total = 1): void {
		$this->setTotal($gadget, $this->getTotal($gadget) + $total);
	}

	public function takeTotal(Gadget|int $gadget, int $total = 1): void {
		$this->setTotal($gadget, max(0, $this->getTotal($gadget) - $total));
	}

	public function getDefaultGadget(bool $gadget = false): Gadget|int {
		return $gadget ? Core::getInstance()->getGadgets()->getGadget($this->defaultGadget) : $this->defaultGadget;
	}

	public function hasDefaultGadget(): bool {
		return $this->getDefaultGadget() !== -1;
	}

	public function setDefaultGadget(Gadget|int $gadget = -1): void {
		$this->defaultGadget = ($gadget instanceof Gadget ? $gadget->getId() : $gadget);
		$this->setChanged();

		if (Core::thisServer()->getType() == "lobby" && ($player = $this->getPlayer()) !== null) {
			if (
				method_exists($player->getGameSession(), "getHotbar") &&
				($hotbar = $player->getGameSession()->getHotbar()->getHotbar()) !== null &&
				$hotbar->getName() == "spawn"
			) {
				if ($this->hasDefaultGadget()) {
					$item = clone ($dg = $this->getDefaultGadget(true))->getItem();
					$item->setCustomName($item->getCustomName() . TextFormat::GRAY . " (" . number_format($this->getTotal($dg)) . " left)");
					$player->getInventory()->setItem(2, $item);
				} else {
					$player->getInventory()->setItem(2, VanillaBlocks::AIR()->asItem());
				}
			}
		}
	}

	public function getLastUse(Gadget|int $gadget): float {
		return $this->uses[$gadget instanceof Gadget ? $gadget->getId() : $gadget] ?? 0;
	}

	public function hasDelay(Gadget|int $gadget): bool {
		$gadget = $gadget instanceof Gadget ? $gadget->getId() : $gadget;
		return isset($this->uses[$gadget]) && microtime(true) < $this->uses[$gadget] + Core::getInstance()->getGadgets()->getGadget($gadget)->getDelay();
	}

	public function getDelayLeft(Gadget|int $gadget): float {
		return round(
			($this->uses[$gadget = ($gadget instanceof Gadget ? $gadget->getId() : $gadget)] ?? 0) + Core::getInstance()->getGadgets()->getGadget($gadget)->getDelay() - microtime(true),
			2
		);
	}

	public function setLastUse(Gadget|int $gadget): void {
		$this->uses[$gadget instanceof Gadget ? $gadget->getId() : $gadget] = microtime(true);
	}

	public function createTables(): void {
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach ([
			"CREATE TABLE IF NOT EXISTS gadgets(xuid BIGINT(16) NOT NULL UNIQUE, gadgets VARCHAR(5000) NOT NULL DEFAULT '{}', defaultGadget INT NOT NULL DEFAULT -1);",
		] as $query) $db->query($query);
	}

	public function loadAsync(): void {
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM gadgets WHERE xuid=?", [$this->getXuid()]));
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null): void {
		$result = $request->getQuery()->getResult();
		$rows = (array) $result->getRows();
		if (count($rows) > 0) {
			$data = array_shift($rows);
			$this->gadgets = json_decode($data["gadgets"], true);
			$this->defaultGadget = $data["defaultGadget"];
		}

		parent::finishLoadAsync($request);
	}

	public function verifyChange(): bool {
		$verify = $this->getChangeVerify();
		return $verify["gadgets"] !== $this->getGadgets() ||
			$verify["default"] !== $this->getDefaultGadget();
	}

	public function saveAsync(): void {
		if (!$this->isLoaded()) return;

		$this->setChangeVerify([
			"gadgets" => $this->getGadgets(),
			"default" => $this->getDefaultGadget()
		]);

		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery(
			"main",
			"INSERT INTO gadgets(xuid, gadgets, defaultGadget) VALUES(?, ?, ?) ON DUPLICATE KEY UPDATE gadgets=VALUES(gadgets), defaultGadget=VALUES(defaultGadget)",
			[$this->getXuid(), json_encode($this->getGadgets()), $this->getDefaultGadget()]
		));
		$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		parent::saveAsync();
	}

	public function save(): bool {
		if (!$this->isLoaded()) return false;

		$xuid = $this->getXuid();
		$gadgets = json_encode($this->getGadgets());
		$default = $this->getDefaultGadget();

		$db = $this->getSession()->getSessionManager()->getDatabase();
		$stmt = $db->prepare("INSERT INTO gadgets(xuid, gadgets, defaultGadget) VALUES(?, ?, ?) ON DUPLICATE KEY UPDATE gadgets=VALUES(gadgets), defaultGadget=VALUES(defaultGadget)");
		$stmt->bind_param("isi", $xuid, $gadgets, $default);
		$stmt->execute();
		$stmt->close();

		return parent::save();
	}

	public function getSerializedData(): array {
		return [
			"gadgets" => json_encode($this->getGadgets()),
			"defaultGadget" => $this->getDefaultGadget()
		];
	}

	public function applySerializedData(array $data): void {
		$this->gadgets = json_decode($data["gadgets"], true);
		$this->defaultGadget = $data["defaultGadget"];
	}
}
