<?php

namespace core\settings\command;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use core\{
	Core,
	AtPlayer as Player
};
use core\settings\ui\SettingsUi;

class SettingsCommand extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		$sender->showModal(new SettingsUi());
	}

	public function getPlugin(): \pocketmine\plugin\Plugin {
		return $this->plugin;
	}
}
