<?php

namespace core\chat\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use core\{
	Core,
	AtPlayer as Player
};
use core\chat\emoji\ui\EmojisUi;
use core\command\type\CoreCommand;

class Emojis extends CoreCommand {

	public $plugin;

	public function __construct(Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["emojilist", "emoji"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args): void {
		/** @var Player $sender */
		if ($this->hasPermission($sender)) $sender->showModal(new EmojisUi());
	}

	public function getPlugin(): Core {
		return $this->plugin;
	}
}
