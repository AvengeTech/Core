<?php

namespace core\network\protocol;

use core\network\server\ServerInstance;

class ServerSetStatusPacket extends OneWayPacket {

	const PACKET_ID = self::SERVER_SET_STATUS;

	public function verifyHandle(): bool {
		$data = $this->getPacketData();
		return isset($data["online"]) && isset($data["identifier"]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		$data = $this->getPacketData();
		$online = $data["online"];
		$identifier = $data["identifier"];
		$server = ($sm = $handler->getServerManager())->getServerById($identifier);
		if ($server instanceof ServerInstance) {
			$server->setOnline($online);
		}
	}
}
