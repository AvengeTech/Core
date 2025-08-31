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
use core\rank\Rank;
use core\utils\TextFormat;

class Queue extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		if (count($args) < 1) {
			$sender->sendMessage(TextFormat::RI . "Usage: /queue <server>");
			return;
		}

		$server = Core::getInstance()->getNetwork()->getServerManager()->getServer(array_shift($args));
		if ($server === null) {
			$sender->sendMessage(TextFormat::RI . "Invalid server!");
			return;
		}
		$queue =
		/**$server->getFullQueue() ?? */
		$server->getQueue();
		if ($queue->hasPlayer($sender)) {
			$queue->removePlayer($sender);
			$sender->sendMessage(TextFormat::RI . "Removed from the " . $server->getIdentifier() . " queue!");
		} else {
			$queue->addPlayer($sender);
			$sender->sendMessage(TextFormat::GI . "Added to the " . $server->getIdentifier() . " queue!");
		}
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
