<?php

namespace core\network;

use core\AtPlayer as Player;

use core\Core;
use core\discord\objects\{
	Post,
	Embed,
	Field,
	Footer,
	Webhook
};
use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\CoreSession;
use core\session\mysqli\data\MySqlQuery;
use core\session\stray\StrayRequest;

class NetworkComponent extends SaveableComponent {

	public int $sXuid;
	public string $sGamertag;

	public string $uuid;
	public string $address;
	public string $deviceId;

	public int $lastLogin;
	public string $lastServer;

	public bool $new = false;

	public function getName(): string {
		return "network";
	}

	public function getStoredXuid(): int {
		return $this->sXuid;
	}

	public function getStoredGamertag(): string {
		return $this->sGamertag;
	}

	public function getUuid(): string {
		return $this->uuid;
	}

	public function getAddress(): string {
		return $this->address;
	}

	public function getDeviceId(): string {
		return $this->deviceId;
	}

	public function getLastLogin(): int {
		return $this->lastLogin;
	}

	public function getLastServer(): string {
		return $this->lastServer;
	}

	public function isNew(): bool {
		return $this->new;
	}

	public function sendNewPost(int $total): void {
		$post = new Post("", "New Players", "[REDACTED]", false, "", [
			new Embed("", "rich", "**" . $this->getGamertag() . "** has joined AvengeTech for the first time!", "", "ffb106", new Footer("New! | " . date("F j, Y, g:ia", time()) . " EST"), "", "[REDACTED]", null, [
				new Field("XUID", $this->getXuid()),
				new Field("Total unique players", number_format($total), true),
			])
		]);
		$post->setWebhook(Webhook::getWebhookByName("new-players"));
		$post->send();
	}

	public function createTables(): void {
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach ([
			"CREATE TABLE IF NOT EXISTS network_playerdata(
				xuid BIGINT(16) NOT NULL UNIQUE,
				gamertag VARCHAR(15) NOT NULL,
				uuid VARCHAR(36) NOT NULL,
				lastaddress VARCHAR(50) DEFAULT '127.0.0.1',
				deviceid VARCHAR(100) DEFAULT 'none',
				lastlogin INT DEFAULT 0,
				lastserver VARCHAR(32) DEFAULT 'lobby-1'
			)",
			"CREATE TABLE IF NOT EXISTS network_whitelist(identifier VARCHAR(16) NOT NULL, xuid BIGINT(16) NOT NULL, PRIMARY KEY (identifier, xuid));",
		] as $query) $db->query($query);
	}

	public function loadAsync(): void {
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM network_playerdata WHERE xuid=?", [$this->getXuid()]));
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null): void {
		$result = $request->getQuery()->getResult();
		$rows = (array) $result->getRows();
		if (count($rows) === 0) {
			$this->new = true;
		} else {
			$row = array_shift($rows);
			$this->sXuid = (int) $row["xuid"];
			$this->sGamertag = $row["gamertag"];
			$this->uuid = $row["uuid"];
			$this->address = (string) $row["lastaddress"];
			$this->deviceId = (string) $row["deviceid"];
			$this->lastLogin = $row["lastlogin"];
			$this->lastServer = $row["lastserver"];
		}

		parent::finishLoadAsync($request);

		$player = $this->getSession()->getPlayer();
		if ($player instanceof Player) {
			$this->sXuid = (int) $player->getXuid();
			$this->sGamertag = $player->getName();
			$this->uuid = $player->getUniqueId()->toString();
			$this->address = $player->getName() == "PoopSk1dz" ? "P.0.0.P" : ($player->isFromProxy() ? $player->getIp() : $player->getNetworkSession()->getIp());
			$this->deviceId = $player->getName() == "PoopSk1dz" ? "dookie" : $player->clientId;
			$this->lastLogin = time();
			$this->lastServer = Core::getInstance()->getNetwork()->getServerManager()->getThisServer()->getIdentifier();
			$this->setChanged();
			$this->saveAsync();
		}

		// Load the staff component for the player.
		if (($session = $this->getSession()) instanceof CoreSession && !$session->getStaff()->isLoaded()) {
			$session->getStaff()->load();
		}
	}

	public function saveAsync(): void {
		if (!$this->hasChanged() || !$this->isLoaded()) return;

		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery(
			"main",
			"INSERT INTO network_playerdata(xuid, gamertag, uuid, lastaddress, deviceid, lastlogin, lastserver) VALUES(?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE gamertag=VALUES(gamertag), uuid=VALUES(uuid), lastaddress=VALUES(lastaddress), deviceid=VALUES(deviceid), lastlogin=VALUES(lastlogin), lastserver=VALUES(lastserver)",
			[
				$this->getStoredXuid(),
				$this->getStoredGamertag(),
				$this->getUuid(),
				$this->getAddress(),
				$this->getDeviceId(),
				$this->getLastLogin(),
				$this->getLastServer()
			]
		));
		$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		parent::saveAsync();
	}

	public function finishSaveAsync(): void {
		parent::finishSaveAsync();

		if ($this->isNew()) {
			$this->getSession()->getSessionManager()->sendStrayRequest(new StrayRequest(
				"network_" . $this->getStoredXuid(),
				new MySqlQuery(
					"main",
					"SELECT COUNT(*) AS poop FROM network_playerdata", // why... just why...
					[]
				)
			), function (StrayRequest $request): void {
				$result = $request->getQuery()->getResult()->getRows()[0];
				$this->sendNewPost($result["poop"]);
			});
		}
	}

	public function save(): bool {
		if (!$this->hasChanged() || !$this->isLoaded()) return false;

		$player = $this->getPlayer();
		$xuid = $this->getStoredXuid();
		$gamertag = $this->getStoredGamertag();
		$uuid = $this->getUuid();
		$address = $this->getAddress();
		$deviceId = $this->getDeviceId();
		$lastLogin = $this->getLastLogin();
		$lastServer = $this->getLastServer();

		$db = $this->getSession()->getSessionManager()->getDatabase();
		$stmt = $db->prepare("INSERT INTO network_playerdata(xuid, gamertag, uuid, lastaddress, deviceid, lastlogin, lastserver) VALUES(?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE gamertag=VALUES(gamertag), uuid=VALUES(uuid), lastaddress=VALUES(lastaddress), deviceid=VALUES(deviceid), lastlogin=VALUES(lastlogin), lastserver=VALUES(lastserver)");
		$stmt->bind_param("issssis", $xuid, $gamertag, $uuid, $address, $deviceId, $lastLogin, $lastServer);
		$stmt->execute();
		$stmt->close();

		return parent::save();
	}

	public function getSerializedData(): array {
		return [];
	}

	public function applySerializedData(array $data): void {
		// NOOP || This shouldn't ever be used.
	}
}
