<?php

namespace core\network\protocol;

use core\AtPlayer as Player;

class ServerPostPlayersPacket extends ConnectPacket {

	const PACKET_ID = self::SERVER_POST_PLAYERS;

	public function timeout(ConnectPacketHandler $handler): void {
		echo "Couldn't post players to server, timed out", PHP_EOL;
	}

	public function addPlayer(Player $player): void {
		if (!isset($this->data["players"])) {
			$this->data["players"] = [];
		}
		$nick = "";
		if ($player->getSession()?->getRank()->hasNick() && $player->getSession()?->getRank()->hasSub()) $nick = "-" . $player->getSession()->getRank()->getNick();
		$this->data["players"][] = $player->getName() . "-" . $player->getXuid() . $nick;
	}
}
