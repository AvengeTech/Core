<?php

namespace core\utils;

use pocketmine\Server;
use pocketmine\scheduler\AsyncTask;

use core\AtPlayer as Player;

class VpnDetectTask extends AsyncTask{

	const KEY = "[REDACTED]";

	public $player;
	public $ip;

	public function __construct(Player $player, string $ip) {
		$this->player = $player->getName();
		$this->ip = $ip;
	}

	public function onRun(): void {
		$ip = $this->ip;
		$key = self::KEY;

		/**$ch = curl_init();
		 * curl_setopt_array($ch, [
		 * CURLOPT_URL => "http://v2.api.iphub.info/ip/{$ip}",
		 * CURLOPT_RETURNTRANSFER => true,
		 * CURLOPT_HTTPHEADER => ["X-Key: {$key}"]
		 * ]);
		 * $this->setResult(json_decode(curl_exec($ch)));
		 * curl_close($ch);*/

		$this->setResult(array_merge(["used_address" => $ip], json_decode(file_get_contents("http://proxycheck.io/v2/" . $ip . "?key=" . $key . "&vpn=1"), true)));
	}

	public function onCompletion(): void {
		$player = Server::getInstance()->getPlayerExact($this->player);
		$r = $this->getResult();
		if ($player instanceof Player) {
			$player->returnVpnCheck($r);
		}
		//var_dump($r);
	}
}
