<?php

namespace core\network\protocol;

use core\AtPlayer;
use pocketmine\Server;

class PlayerChatPacket extends OneWayPacket {

	const PACKET_ID = self::PLAYER_CHAT;

	public function verifyHandle(): bool {
		$data = $this->getPacketData();
		return isset($data["player"], $data["message"], $data["server"], $data["formatted"], $data["format"], $data["rank"]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		$data = $this->getPacketData();
		$format = $data["format"];
		$rank = $data["rank"];
		$message = $data["message"];
		$preformatted = $data["formatted"];
		foreach (Server::getInstance()->getOnlinePlayers() as $p) {
			/** @var AtPlayer $p */
			$p->sendChatMessage($format, $rank, $message, $preformatted);
		}
	}
}
