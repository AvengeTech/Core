<?php

namespace core\utils;

use pocketmine\player\Player;
use pocketmine\scheduler\AsyncTask;

use core\Core;
use core\discord\objects\{
	Post,
	Embed,
	Field,
	Footer,
	Webhook
};

class SaveUserDataTask extends AsyncTask{

	public $gamertag;
	public $xuid;
	public $uuid;
	public $address;
	public $deviceId;
	public $server;

	public $new = false;
	public $total = 0;

	public function __construct(Player $player) {
		$this->gamertag = $player->getName();
		$this->xuid = $player->getXuid();
		$this->uuid = $player->getUniqueId();
		$this->address = $player->getNetworkSession()->getIp();
		$this->deviceId = $player->clientId;
		$this->server = Core::getInstance()->getNetwork()->getServerManager()->getThisServer()->getIdentifier();
	}

	public function onRun(): void {
		$creds = array_merge(file("[REDACTED]"), ["core"]);
		foreach ($creds as $key => $cred) $creds[$key] = trim(str_replace("\n", "", $cred));
		try {
			$db = new \mysqli(...$creds);
		} catch (\Exception $e) {
			echo "error creating database while trying to save player data [GAMERTAG: " . $this->gamertag . " - XUID: " . $this->xuid . "]", PHP_EOL;
			return;
		}

		$xuid = $this->xuid;
		$stmt = $db->prepare("SELECT xuid FROM network_playerdata WHERE xuid=?");
		$stmt->bind_param("i", $xuid);
		$stmt->bind_result($x);
		if ($stmt->execute()) {
			$stmt->fetch();
		}
		$stmt->close();
		if ($x == null) {
			$this->new = true;
		}

		$xuid = $this->xuid;
		$gamertag = $this->gamertag;
		$uuid = $this->uuid;
		$address = $this->address;
		$deviceid = $this->deviceId;
		$lastloggedin = time();
		$server = $this->server;

		$stmt = $db->prepare("INSERT INTO network_playerdata(xuid, gamertag, uuid, lastaddress, deviceid, lastlogin, lastserver) VALUES(?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE gamertag=VALUES(gamertag), uuid=VALUES(uuid), lastaddress=VALUES(lastaddress), deviceid=VALUES(deviceid), lastlogin=VALUES(lastlogin), lastserver=VALUES(lastserver)");
		$stmt->bind_param("issssis", $xuid, $gamertag, $uuid, $address, $deviceid, $lastloggedin, $server);
		$stmt->execute();
		$stmt->close();

		if ($this->new) {
			$stmt = $db->prepare("SELECT COUNT(*) FROM network_playerdata");
			$stmt->bind_result($count);
			if ($stmt->execute()) {
				$stmt->fetch();
				$this->total = $count;
			}
			$stmt->close();
		}

		$db->close();
	}

	public function onCompletion(): void {
		if ($this->new) {
			$post = new Post("", "New Players", "[REDACTED]", false, "", [
				new Embed("", "rich", "**" . $this->gamertag . "** has joined AvengeTech for the first time!", "", "ffb106", new Footer("Welcome! | " . date("F j, Y, g:ia", time()) . " EST"), "", "[REDACTED]", null, [
					new Field("XUID", $this->xuid),
					new Field("Total unique players", number_format($this->total), true),
				])
			]);
			$post->setWebhook(Webhook::getWebhookByName("new-players"));
			$post->send();
		}
	}
}
