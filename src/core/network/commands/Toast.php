<?php

namespace core\network\commands;

use pocketmine\Server;
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

class Toast extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) < 3) {
			$sender->sendMessage(TextFormat::RI . "Usage: /toast <name:bc> <title> <body>");
			return;
		}

		$name = array_shift($args);
		$title = array_shift($args);
		$body = array_shift($args);

		if ($name !== "bc") {
			$player = Server::getInstance()->getPlayerByPrefix($name);
			if ($player instanceof Player) {
				Core::broadcastToast($title, $body, [$player]);
				$sender->sendMessage(TextFormat::GI . "Sent toast to " . $player->getName());
			}
		} else {
			Core::broadcastToast($title, $body);
			$sender->sendMessage(TextFormat::GI . "Broadcasted toast");
		}
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
