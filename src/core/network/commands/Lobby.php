<?php

namespace core\network\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player
};
use core\command\type\CoreCommand;
use core\utils\TextFormat;

class Lobby extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["hub"]);
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		$network = Core::getInstance()->getNetwork();
		if ($network->getServerType() == "lobby") {
			$sender->sendMessage(TextFormat::RI . "You are already in a lobby.");
			return false;
		}
		$lobby = ($sm = Core::getInstance()->getNetwork()->getServerManager())->getLeastPopulated("lobby");
		if ($lobby === null) {
			$sender->sendMessage(TextFormat::RI . "No hubs are currently available, please try again soon!");
			return;
		}
		if ($lobby->getIdentifier() === Core::thisServer()->getIdentifier()) {
			foreach ($sm->getServersByType("lobby") as $server) {
				if (
					$server->isOnline() &&
					!$server->isTestServer() &&
					$server->getIdentifier() !== Core::thisServer()->getIdentifier()
				) {
					$lobby = $server;
					break;
				}
			}
		}
		if ($lobby === null) {
			$sender->sendMessage(TextFormat::RI . "No hubs are currently available, please try again soon!");
			return;
		}
		$lobby->transfer($sender, TextFormat::GI . "Sent to open Lobby!");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
