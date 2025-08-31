<?php

namespace core\network\protocol;

use core\network\Structure;

class ServerGetPlayersPacket extends ConnectPacket {

	const PACKET_ID = self::SERVER_GET_PLAYERS;

	public function timeoutReturn(ConnectPacketHandler $handler): void {
		echo "Unable to return player counts", PHP_EOL;
	}

	public function verifyHandle(): bool {
		$data = $this->getPacketData();
		return isset($data["identifier"]) && isset(Structure::SOCKET_PORTS[$data["identifier"]]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		$data = $this->getPacketData();
		$identifier = $data["identifier"];
		$server = $handler->getServerManager()->getServer($identifier);
		$players = [];
		if ($server !== null) {
			foreach ($server->getCluster()->getPlayers() as $player) {
				$players[] = $player->getGamertag() . "-" . $player->getXuid() . ($player->getUser()->hasNick() ? "-" . $player->getUser()->getNick() : "");
			}
			$response = [
				"error" => false,
				"message" => "Successfully returned player data of " . $identifier,
				"identifier" => $identifier,
				"players" => $players
			];
		} else {
			$response = [
				"error" => true,
				"message" => "Invalid server provided!"
			];
		}
		$this->setResponseData($response);
	}

	public function verifyResponse(): bool {
		$response = $this->getResponseData();
		return isset($response["players"]) && isset($response["identifier"]);
	}
}
