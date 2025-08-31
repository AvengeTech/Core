<?php

namespace core\network\protocol;

use core\Core;

class PlayerReconnectPacket extends OneWayPacket {

	const PACKET_ID = self::PLAYER_RECONNECT;

	public function verifyHandle(): bool {
		$data = $this->getPacketData();
		return isset($data["player"], $data["server"], $data["restart"]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		$data = $this->getPacketData();
		$sm = Core::getInstance()->getNetwork()->getServerManager();
		$sm->addReconnect($data["player"], $data["server"], 15, $data["restart"]);
	}
}
