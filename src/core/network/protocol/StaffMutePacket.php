<?php

namespace core\network\protocol;

use pocketmine\Server;

use core\AtPlayer as Player;
use core\staff\entry\MuteEntry;
use core\utils\TextFormat;

class StaffMutePacket extends OneWayPacket {

	const PACKET_ID = self::STAFF_MUTE;

	public function verifyHandle(): bool {
		$data = $this->getPacketData();
		return isset($data["player"], $data["by"], $data["when"], $data["identifier"], $data["length"], $data["reason"]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		$data = $this->getPacketData();
		$player = Server::getInstance()->getPlayerExact($data["player"]);
		if ($player instanceof Player) {
			$until = $data["length"];
			$length = ($until == -1 ? "ETERNITY" : floor(($until - time()) / 86400) . " days");

			$player->getSession()->getStaff()->getMuteManager()->addMute(
				new MuteEntry(
					$player->getUser(),
					$data["by"],
					$data["reason"],
					$data["identifier"],
					$data["when"],
					$until
				)
			);
			$player->sendMessage(TextFormat::RI . "You have been muted by " . TextFormat::YELLOW . $data["by"] . TextFormat::GRAY . "! Reason: " . TextFormat::AQUA . "'" . $data["reason"] . "'" . TextFormat::GRAY . " - Length: " . $length);
		}
	}
}
