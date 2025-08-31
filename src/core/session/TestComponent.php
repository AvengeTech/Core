<?php

namespace core\session;

use core\AtPlayer as Player;
use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\MySqlQuery;

class TestComponent extends SaveableComponent {

	public int $xuid = 0;
	public string $uuid = "";
	public string $gamertag = "";

	public function getName(): string {
		return "test";
	}

	public function getSetXuid(): int {
		return $this->xuid;
	}

	public function getSetUuid(): string {
		return $this->uuid;
	}

	public function getSetGamertag(): string {
		return $this->gamertag;
	}

	public function createTables(): void {
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach ([] as $query) $db->query($query);
	}

	public function loadAsync(): void {
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM network_playerinfo WHERE xuid=?", [$this->getXuid()]));
		//$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();

		echo "Load request sent for " . $this->getName() . " component", PHP_EOL;
	}

	public function finishLoadAsync(?ComponentRequest $request = null): void {
		$result = $request->getQuery()->getResult();
		$rows = (array) $result->getRows();
		if (count($rows) > 0) {
			$data = array_shift($rows);
			$this->xuid = $data["xuid"];
			$this->uuid = $data["uuid"];
			$this->gamertag = $data["gamertag"];
		} else {
			$this->xuid = $this->getXuid();
			$this->uuid = ($player = $this->getPlayer()) instanceof Player ? $player->getUniqueId()->toString() : "noplayer";
			$this->gamertag = $this->getGamertag();
			$this->setChanged();
			$this->saveAsync();
		}

		parent::finishLoadAsync($request);
		echo "Finished loading " . $this->getName() . " component", PHP_EOL;
	}

	public function load(): bool {
		$xuid = $this->getXuid();
		$db = $this->getSession()->getSessionManager()->getDatabase();
		$stmt = $db->prepare("SELECT * FROM network_playerinfo WHERE xuid=?");
		$stmt->bind_param("i", $xuid);
		$stmt->bind_result($xx, $uuid, $gamertag);
		if ($stmt->execute()) {
			$stmt->fetch();
			if ($xx != null) {
				$this->xuid = $xx;
				$this->gamertag = $gamertag;
			}
		}
		$stmt->close();
		return true;
	}

	public function verifyChange(): bool {
		$player = $this->getPlayer();
		$verify = $this->getChangeVerify();
		return ($player instanceof Player ? $player->getUniqueId()->toString() : $this->getSetUuid()) !== $verify["uuid"] ||
			$this->getGamertag() !== $verify["gamertag"];
	}

	public function saveAsync(): void {
		if (!$this->hasChanged() || !$this->isLoaded()) return;

		$this->setChangeVerify([
			"xuid" => $this->getSetXuid(),
			"uuid" => $this->getSetUuid(),
			"gamertag" => $this->getSetGamertag(),
		]);

		$player = $this->getPlayer();
		$uuid = $player instanceof Player ? $player->getUniqueId()->toString() : "dddd";
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "INSERT network_playerinfo(xuid, uuid, gamertag) VALUES(?, ?, ?) ON DUPLICATE KEY UPDATE xuid=VALUES(xuid), uuid=VALUES(uuid), gamertag=VALUES(gamertag)", [$this->getXuid(), $uuid, $this->getGamertag()]));
		$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		parent::saveAsync();

		echo $this->getName() . " component started saving async", PHP_EOL;
	}

	public function finishSaveAsync(): void {
		parent::finishSaveAsync();

		echo $this->getName() . " component finished saving async", PHP_EOL;
	}

	public function save(): bool {
		if (!$this->hasChanged()) return false;

		$player = $this->getPlayer();
		$uuid = $player instanceof Player ? $player->getUniqueId()->toString() : "dddd";
		$xuid = $this->getXuid();

		$db = $this->getSession()->getSessionManager()->getDatabase();
		$stmt = $db->prepare("INSERT network_playerinfo(xuid, uuid, gamertag) VALUES(?, ?, ?) ON DUPLICATE KEY UPDATE xuid=VALUES(xuid), uuid=VALUES(uuid), gamertag=VALUES(gamertag)");
		$stmt->bind_param("iss", $xuid, $uuid, $gamertag);
		$stmt->execute();
		$stmt->close();

		echo $this->getName() . " component saved on main thread", PHP_EOL;
		return parent::save();
	}
}
