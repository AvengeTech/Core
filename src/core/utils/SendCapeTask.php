<?php

namespace core\utils;

use pocketmine\{
	player\Player,
	Server
};
use pocketmine\scheduler\Task;

use core\utils\CapeData;

class SendCapeTask extends Task{

	public $player;
	public $cape = "";

	public function __construct(Player $player, string $cape) {
		$this->player = $player->getName();
		$this->cape = $cape;
	}

	public function onRun(): void {
		$player = Server::getInstance()->getPlayerExact($this->player);
		if ($player instanceof Player) {
			$player->setSkin((new CapeData())->getSkinWithCape($player, $this->cape));
			$player->sendSkin();
		}
	}
}
