<?php

namespace core\network\protocol;

use core\network\server\player\ConnectionData;
use core\user\User;

class ServerGetAllPlayersPacket extends ConnectPacket {

	const PACKET_ID = self::SERVER_GET_ALL_PLAYERS;

	public function getDefaultPacketData(): array {
		return ["players" => []];
	}

	public function verifyResponse(): bool {
		$response = $this->getResponseData();
		return isset($response["players"]);
	}

	public function handleResponse(ConnectPacketHandler $handler): void {
		$response = $this->getResponseData();
		foreach ($handler->getServerManager()->getServers() as $server) {
			$cluster = $server->getCluster();
			$players = [];
			foreach ($response["players"][$server->getIdentifier()] ?? [] as $player) {
				$pdata = explode("-", $player);
				if (!isset($pdata[2])) $pdata[2] = null;
				$data = new ConnectionData(new User($pdata[1], $pdata[0], false, "default", $pdata[2]), $server->getIdentifier());
				$players[strtolower($data->getUser()->getGamertag())] = $data;
			}
			$cluster->setPlayers($players);
		}
	}
}
