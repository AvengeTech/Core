<?php

namespace core\network\protocol;

use core\network\server\ServerInstance;

class ServerWhitelistPacket extends OneWayPacket {

	const PACKET_ID = self::SERVER_WHITELIST;

	public function verifyHandle(): bool {
		$data = $this->getPacketData();
		return isset($data["identifier"]) && isset($data["whitelisted"]) && isset($data["whitelist"]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		$data = $this->getPacketData();
		$identifier = $data["identifier"];
		$whitelisted = $data["whitelisted"];
		$whitelist = $data["whitelist"];
		$server = $handler->getServerManager()->getServerById($identifier);
		if ($server instanceof ServerInstance && !$server->isThis()) {
			$server->updateWhitelist($whitelisted, $whitelist);
			echo "Received whitelist update for " . $identifier, PHP_EOL;
		}
	}
}
