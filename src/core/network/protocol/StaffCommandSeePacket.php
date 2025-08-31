<?php

namespace core\network\protocol;

use core\Core;

class StaffCommandSeePacket extends OneWayPacket {

	const PACKET_ID = self::STAFF_COMMAND_SEE;

	public function verifyHandle(): bool {
		$data = $this->getPacketData();
		return isset($data["sender"], $data["command"], $data["identifier"]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		$data = $this->getPacketData();
		Core::getInstance()->getStaff()->sendCommandSee($data["sender"], $data["command"], $data["identifier"]);
	}
}
