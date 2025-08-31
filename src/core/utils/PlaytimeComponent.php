<?php

namespace core\utils;

use core\AtPlayer as Player;
use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\MySqlQuery;

class PlaytimeComponent extends SaveableComponent{

	public bool $firstJoin = true;

	public int $playtime = 0;
	public int $playcache = 0;

	public function getName(): string {
		return "playtime";
	}

	public function isFirstJoin(): bool {
		return $this->firstJoin;
	}

	public function getPlaytime(): int {
		return $this->playtime;
	}

	public function getFormattedPlaytime(bool $withcache = false): string {
		$seconds = $this->getPlaytime() + ($withcache ? $this->getAddedPlaytime() : 0);
		$dtF = new \DateTime("@0");
		$dtT = new \DateTime("@$seconds");
		return $dtF->diff($dtT)->format("%a days, %h hours, %i minutes");
	}

	public function setPlaytime(int $value): void {
		$this->playtime = $value;
		$this->setChanged();
	}

	public function addPlaytime(int $value = 1): void {
		$this->setPlaytime($this->getPlaytime() + $value);
	}

	public function getPlayCache(): int {
		return $this->playcache;
	}

	public function getAddedPlaytime(): int {
		return $this->getPlayCache() == 0 || !$this->getPlayer() instanceof Player ? 0 : time() - $this->getPlayCache();
	}

	public function createTables(): void {
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach ([
			"CREATE TABLE IF NOT EXISTS playtime(xuid BIGINT(16) NOT NULL UNIQUE, playtime INT NOT NULL DEFAULT 0);",
		] as $query) $db->query($query);
	}

	public function loadAsync(): void {
		$this->playcache = time();

		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM playtime WHERE xuid=?", [$this->getXuid()]));
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null): void {
		$result = $request->getQuery()->getResult();
		$rows = (array) $result->getRows();
		if (count($rows) > 0) {
			$data = array_shift($rows);
			$this->playtime = $data["playtime"];
			$this->firstJoin = false;
		}

		parent::finishLoadAsync($request);
	}

	public function saveAsync(): void {
		if (!$this->isLoaded()) return;

		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery(
			"main",
			"INSERT INTO playtime(xuid, playtime) VALUES(?, ?) ON DUPLICATE KEY UPDATE playtime=VALUES(playtime)",
			[$this->getXuid(), $this->getPlaytime() + $this->getAddedPlaytime()]
		));
		$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		parent::saveAsync();
	}

	public function save(): bool {
		if (!$this->isLoaded()) return false;

		$xuid = $this->getXuid();
		$playtime = $this->getPlaytime() + $this->getAddedPlaytime();

		$db = $this->getSession()->getSessionManager()->getDatabase();
		$stmt = $db->prepare("INSERT INTO playtime(xuid, playtime) VALUES(?, ?) ON DUPLICATE KEY UPDATE playtime=VALUES(playtime)");
		$stmt->bind_param("ii", $xuid, $playtime);
		$stmt->execute();
		$stmt->close();

		return parent::save();
	}

	public function getSerializedData(): array {
		return [
			"playtime" => $this->getPlaytime() + $this->getAddedPlaytime()
		];
	}

	public function applySerializedData(array $data): void {
		$this->playtime = $data["playtime"];
		$this->firstJoin = false;
	}
}
