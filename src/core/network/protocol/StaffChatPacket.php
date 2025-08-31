<?php

namespace core\network\protocol;

use core\Core;

class StaffChatPacket extends OneWayPacket {

	const PACKET_ID = self::STAFF_CHAT;

	public function verifyHandle(): bool {
		$data = $this->getPacketData();
		return isset($data["sender"], $data["message"], $data["identifier"]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		$data = $this->getPacketData();
		Core::getInstance()->getStaff()->sendStaffMessage($data["sender"], $data["message"], $data["identifier"]);
	}
}
