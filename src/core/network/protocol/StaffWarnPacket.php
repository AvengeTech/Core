<?php

namespace core\network\protocol;

use pocketmine\Server;

use core\AtPlayer as Player;
use core\staff\entry\WarnEntry;
use core\utils\TextFormat;

class StaffWarnPacket extends OneWayPacket {

	const PACKET_ID = self::STAFF_WARN;

	public function verifyHandle(): bool {
		$data = $this->getPacketData();
		return isset($data["player"], $data["by"], $data["reason"], $data["identifier"], $data["when"]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		$data = $this->getPacketData();
		$player = Server::getInstance()->getPlayerExact($data["player"]);
		if ($player instanceof Player) {
			$player->sendMessage(TextFormat::RI . "You have been warned by " . TextFormat::YELLOW . $data["by"] . TextFormat::GRAY . "! Reason: " . TextFormat::AQUA . $data["reason"]);
			$warn = new WarnEntry($player->getUser(), $data["by"], $data["reason"], $data["identifier"], $data["when"]);
			$player->getSession()?->getStaff()->getWarnManager()->addWarn($warn);
		}
	}
}
