<?php

namespace core\network\protocol;

use pocketmine\Server;

use core\AtPlayer as Player;
use core\utils\TextFormat;

class StaffBanPacket extends OneWayPacket {

	const PACKET_ID = self::STAFF_BAN;

	public function verifyHandle(): bool {
		$data = $this->getPacketData();
		return isset($data["player"], $data["by"], $data["length"], $data["reason"]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		$data = $this->getPacketData();
		$player = Server::getInstance()->getPlayerExact($data["player"]);
		if ($player instanceof Player) {
			$until = $data["length"];
			$length = ($until == -1 ? "ETERNITY" : floor(($until - time()) / 86400) . " days");

			$player->kick(
				TextFormat::RED . "You were banned by " . TextFormat::YELLOW . $data["by"] . PHP_EOL .
					TextFormat::RED . "Reason: " . TextFormat::YELLOW . "'" . $data["reason"] . "'" . PHP_EOL .
					TextFormat::RED . "Length: " . TextFormat::YELLOW . $length . PHP_EOL .
					TextFormat::RED . "Appeal for an unban at " . TextFormat::YELLOW . "avengetech.net/discord",
				false
			);
		}
	}
}
