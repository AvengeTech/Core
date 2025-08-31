<?php

namespace core\network\protocol;

use pocketmine\Server;

use skyblock\utils\LoadAction as SkyBlockLoadAction;
use prison\utils\LoadAction as PrisonLoadAction;

use core\{
	Core,
	AtPlayer as Player
};
use core\utils\{
	LoadAction,
};

class PlayerLoadActionPacket extends OneWayPacket {

	const PACKET_ID = self::PLAYER_LOAD_ACTION;

	public function verifyHandle(): bool {
		$data = $this->getPacketData();
		return isset($data["player"], $data["server"], $data["action"]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		$data = $this->getPacketData();
		$player = Server::getInstance()->getPlayerExact($data["player"]);
		$action = $this->toLoadAction();
		if ($player instanceof Player) {
			$player->addPreLoadAction($action);
			$player->addLoadAction($action);
		} else {
			Core::getInstance()->addLoadAction($action);
		}
	}

	public function toLoadAction(): LoadAction {
		$data = $this->getPacketData();
		switch (Core::getInstance()->getNetwork()->getServerManager()->getThisServer()->getType()) {
			case "skyblock":
				return new SkyBlockLoadAction($data["player"], $data["action"], (array) ($data["actionData"] ?? []));
			case "prison":
				return new PrisonLoadAction($data["player"], $data["action"], (array) ($data["actionData"] ?? []));
			default:
				return new LoadAction($data["player"], $data["action"], (array) ($data["actionData"] ?? []));
		}
	}
}
