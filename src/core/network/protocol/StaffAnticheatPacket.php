<?php

namespace core\network\protocol;

use core\Core;
use core\utils\TextFormat;

class StaffAnticheatPacket extends OneWayPacket {

	const PACKET_ID = self::STAFF_ANTICHEAT_NOTICE;

	public function verifyHandle(): bool {
		$data = $this->getPacketData();
		return isset($data["message"]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		$data = $this->getPacketData();
		Core::getInstance()->getStaff()->anticheatAlert(TextFormat::RI . TextFormat::YELLOW . $data["message"]);
	}
}
