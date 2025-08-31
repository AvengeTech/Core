<?php

namespace core\utils;

use pocketmine\{
	player\Player,
	Server
};
use pocketmine\scheduler\Task;

class KickDelayTask extends Task {

	public $player;
	public $message;

	public function __construct(Player $player, string $message) {
		$this->player = $player->getName();
		$this->message = $message;
	}

	public function onRun(): void {
		$player = Server::getInstance()->getPlayerExact($this->player);
		if ($player instanceof Player) {
			$player->kick($this->message, false);
		}
	}
}
