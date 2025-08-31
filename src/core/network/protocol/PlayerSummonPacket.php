<?php

namespace core\network\protocol;

use pocketmine\Server;

use core\AtPlayer as Player;
use core\network\server\ServerInstance;
use core\utils\TextFormat;

class PlayerSummonPacket extends OneWayPacket {

	const PACKET_ID = self::PLAYER_SUMMON;

	public function send(ConnectPacketHandler $handler): void {
		$data = $this->getPacketData();
		$handler->getServerManager()->getThisServer()->addSummoning($data["player"], $data["sentby"]);
	}

	public function verifyHandle(): bool {
		$data = $this->getPacketData();
		return isset($data["player"], $data["sentby"], $data["to"]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		$data = $this->getPacketData();
		$player = Server::getInstance()->getPlayerExact($data["player"]);
		$server = $handler->getServerManager()->getServerById($data["to"]);
		if ($player instanceof Player && $server instanceof ServerInstance) {
			$server->transfer($player, TextFormat::YI . "Summoned to " . TextFormat::AQUA . $server->getIdentifier() . TextFormat::GRAY . " by " . TextFormat::YELLOW . $data["sentby"]);
		}
	}
}
