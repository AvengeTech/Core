<?php

namespace core\network\protocol;

class ServerSubUpdatePacket extends OneWayPacket {

	const PACKET_ID = self::SERVER_SUB_UPDATE;

	public function verifyHandle(): bool {
		$data = $this->getPacketData();
		return isset($data["server"], $data["type"]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		$data = $this->getPacketData();
		foreach($handler->getServerManager()->getSubUpdateHandlers() as $handler)
			$handler($data["server"], $data["type"], (array) ($data["data"] ?? []));
	}
}
