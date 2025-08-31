<?php

namespace core\cosmetics\command;

use core\Core;
use core\AtPlayer as Player;
use core\command\type\CoreCommand;
use core\cosmetics\ui\CosmeticsUi;
use core\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

class Cosmetics extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args) {
		$sender->showModal(new CosmeticsUi($sender));
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
