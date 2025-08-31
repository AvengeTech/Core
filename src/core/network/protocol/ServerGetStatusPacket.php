<?php

namespace core\network\protocol;

use core\network\server\ServerInstance;

class ServerGetStatusPacket extends ConnectPacket {

	const PACKET_ID = self::SERVER_GET_STATUS;

	public function handle(ConnectPacketHandler $handler): void {
		$this->setResponseData([
			"error" => false,
			"message" => "Server status returned!",
			"online" => true
		]);
	}

	public function verifyResponse(): bool {
		$response = $this->getResponseData();
		return isset($response["statuses"]);
	}

	public function handleResponse(ConnectPacketHandler $handler): void {
		$response = $this->getResponseData();
		$statuses = $response["statuses"];
		foreach ($statuses as $identifier => $status) {
			$server = $handler->getServerManager()->getServerById($identifier);
			if ($server instanceof ServerInstance) {
				$server->setOnline($status);
			}
		}
	}
}
