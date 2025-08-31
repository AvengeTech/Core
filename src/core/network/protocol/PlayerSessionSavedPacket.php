<?php

namespace core\network\protocol;

use pocketmine\Server;

use core\{
	Core,
	AtPlayer as Player
};

class PlayerSessionSavedPacket extends OneWayPacket {

	const PACKET_ID = self::PLAYER_SESSION_SAVED;

	const TYPE_CORE = 0;
	const TYPE_GAME = 1;

	public function verifyHandle(): bool {
		$data = $this->getPacketData();
		return isset($data["player"], $data["server"], $data["type"]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		$data = $this->getPacketData();
		$player = Server::getInstance()->getPlayerExact($name = $data["player"]);
		$type = $data["type"] ?? self::TYPE_CORE;
		if ($player instanceof Player) {
			switch ($type) {
				case self::TYPE_GAME:
					$player->setGameSessionSaved();
					break;
				case self::TYPE_CORE:
				default:
					$player->setSessionSaved();
					break;
			}
		} else {
			switch ($data["type"]) {
				case self::TYPE_GAME:
					Core::getInstance()->getSessionManager()->addGameSessionSaved($name);
					break;
				case self::TYPE_CORE:
				default:
					Core::getInstance()->getSessionManager()->addSessionSaved($name);
					break;
			}
		}
	}
}
