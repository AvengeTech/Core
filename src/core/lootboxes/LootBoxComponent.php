<?php

namespace core\lootboxes;

use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\MySqlQuery;

class LootBoxComponent extends SaveableComponent {

	public int $lootboxes = 0;
	public int $shards = 0;

	public function getName(): string {
		return "lootboxes";
	}

	public function getLootBoxes(): int {
		return $this->lootboxes;
	}

	public function setLootBoxes(int $value): void {
		$this->lootboxes = $value;
		$this->setChanged();
	}

	public function addLootBoxes(int $value = 1): void {
		$this->setLootBoxes($this->getLootBoxes() + $value);
	}

	public function takeLootBoxes(int $value = 1): void {
		$this->setLootBoxes(max(0, $this->getLootBoxes() - $value));
	}

	public function getShards(): int {
		return $this->shards;
	}

	public function setShards(int $value): void {
		$this->shards = $value;
		$this->setChanged();
	}

	public function addShards(int $value = 1): void {
		$this->setShards($this->getShards() + $value);
	}

	public function takeShards(int $value = 1): void {
		$this->setShards(max(0, $this->getShards() - $value));
	}

	public function createTables(): void {
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach ([
			"CREATE TABLE IF NOT EXISTS lootboxes(xuid BIGINT(16) NOT NULL UNIQUE, lootboxes INT NOT NULL DEFAULT 0, shards INT NOT NULL DEFAULT 0);",
		] as $query) $db->query($query);
	}

	public function loadAsync(): void {
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM lootboxes WHERE xuid=?", [$this->getXuid()]));
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null): void {
		$result = $request->getQuery()->getResult();
		$rows = (array) $result->getRows();
		if (count($rows) > 0) {
			$data = array_shift($rows);
			$this->lootboxes = $data["lootboxes"];
			$this->shards = $data["shards"];
		}

		parent::finishLoadAsync($request);
	}

	public function saveAsync(): void {
		if (!$this->isLoaded()) return;

		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery(
			"main",
			"INSERT INTO lootboxes(xuid, lootboxes, shards) VALUES(?, ?, ?) ON DUPLICATE KEY UPDATE lootboxes=VALUES(lootboxes), shards=VALUES(shards)",
			[$this->getXuid(), $this->getLootBoxes(), $this->getShards()]
		));
		$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		parent::saveAsync();
	}

	public function save(): bool {
		if (!$this->isLoaded()) return false;

		$xuid = $this->getXuid();
		$lootboxes = $this->getLootBoxes();
		$shards = $this->getShards();

		$db = $this->getSession()->getSessionManager()->getDatabase();
		$stmt = $db->prepare("INSERT INTO lootboxes(xuid, lootboxes, shards) VALUES(?, ?, ?) ON DUPLICATE KEY UPDATE lootboxes=VALUES(lootboxes), shards=VALUES(shards)");
		$stmt->bind_param("iii", $xuid, $lootboxes, $shards);
		$stmt->execute();
		$stmt->close();

		return parent::save();
	}

	public function getSerializedData(): array {
		return [
			"lootboxes" => $this->getLootBoxes(),
			"shards" => $this->getShards()
		];
	}

	public function applySerializedData(array $data): void {
		$this->lootboxes = $data["lootboxes"];
		$this->shards = $data["shards"];
	}
}
