<?php

namespace core\cosmetics\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player
};
use core\command\type\CoreCommand;
use core\cosmetics\ui\CosmeticListUi;
use core\utils\TextFormat;

class Capes extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		$sender->showModal(new CosmeticListUi($sender, 0, 1, [], false));
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
