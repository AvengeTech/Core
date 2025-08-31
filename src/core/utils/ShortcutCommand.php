<?php

namespace core\utils;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

use core\AtPlayer as Player;

class ShortcutCommand extends Command{

	public $plugin;
	public $shortcuts = [];

	public function __construct(Plugin $plugin, $name, array $shortcuts = []) {
		$this->plugin = $plugin;
		$this->shortcuts = $shortcuts;
		parent::__construct($name, "shortcut");
		$this->setPermission("core.staff");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if ($sender instanceof Player && !$sender->isStaff()) {
			$sender->sendMessage(TextFormat::RI . "You are not allowed to use shortcut commands!");
			return false;
		}
		foreach ($this->getShortcuts() as $shortcut) {
			Server::getInstance()->dispatchCommand($sender, $shortcut);
		}
		return true;
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}

	public function getShortcuts(): array {
		return $this->shortcuts;
	}
}
