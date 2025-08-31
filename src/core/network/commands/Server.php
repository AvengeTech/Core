<?php

namespace core\network\commands;

use core\command\type\CoreCommand;
use pocketmine\command\CommandSender;

use core\Core;
use core\network\Links;
use core\utils\TextFormat;

class Server extends CoreCommand {

	public $plugin;

	public function __construct(Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setAliases(["here", "where"]);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		$network = Core::getInstance()->getNetwork();
		$sender->sendMessage(TextFormat::YELLOW . "You are on: " . TextFormat::LIGHT_PURPLE . $network->getIdentifier() . "." . Links::MAIN);
	}

	public function getPlugin(): Core {
		return $this->plugin;
	}
}
