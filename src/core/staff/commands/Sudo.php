<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\Server;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\AtPlayer as Player;
use core\Core;
use core\utils\TextFormat;

class Sudo extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) < 2) {
			$sender->sendMessage(TextFormat::RI . "Usage: /sudo <player> <command>");
			return;
		}
		$player = Server::getInstance()->getPlayerByPrefix(array_shift($args));
		if (!$player instanceof Player) {
			$sender->sendMessage(TextFormat::RN . "Player not found!");
			return;
		}

		$command = implode(" ", $args);
		Server::getInstance()->dispatchCommand($player, $command);
		$sender->sendMessage(TextFormat::GN . "Successfully ran command " . TextFormat::YELLOW . "/" . $command . TextFormat::GRAY . " as " . $player->getName() . "!");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
