<?php

namespace core\network\protocol;

use pocketmine\Server;

class ServerAnnouncementPacket extends OneWayPacket {

	const PACKET_ID = self::SERVER_ANNOUNCEMENT;

	public function verifyHandle(): bool {
		return isset($this->getPacketData()["message"]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		Server::getInstance()->broadcastMessage($this->getPacketData()["message"]);
	}
}
