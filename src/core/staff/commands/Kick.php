<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\Server;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player,
	AtPlayer
};
use core\utils\TextFormat;

class Kick extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (empty($args)) {
			$sender->sendMessage(TextFormat::RN . "No player provided.");
			return;
		}

		$player = Server::getInstance()->getPlayerByPrefix(array_shift($args));

		if (!$player instanceof AtPlayer) {
			$sender->sendMessage(TextFormat::RN . "Player not online!");
			return;
		}
		if (empty($args)) {
			$sender->sendMessage(TextFormat::RN . "Must provide reason!");
			return;
		}
		$player->kickPlayer($sender instanceof AtPlayer ? $sender : "CONSOLE", implode(" ", $args));
		$sender->sendMessage(TextFormat::GN . $player->getName() . " has been kicked!");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
