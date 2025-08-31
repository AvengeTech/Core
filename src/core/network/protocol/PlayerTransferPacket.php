<?php

namespace core\network\protocol;

class PlayerTransferPacket extends OneWayPacket {

	const PACKET_ID = self::PLAYER_TRANSFER;

	public function verifyHandle(): bool {
		$data = $this->getPacketData();
		return isset($data["player"], $data["from"], $data["message"]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		$data = $this->getPacketData();
		$player = $data["player"];
		$from = $data["from"];
		$message = $data["message"];
		$server = $handler->getServerManager()->getThisServer();
		$server->addPendingConnection($player, $from, $message);
	}
}
