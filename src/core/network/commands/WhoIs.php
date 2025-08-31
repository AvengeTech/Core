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
use core\user\User;
use core\utils\TextFormat;

class WhoIs extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) < 1) {
			$sender->sendMessage(TextFormat::RI . "Usage: /whois <nickname>");
			return;
		}

		Core::getInstance()->getRank()->userByNick(($nick = array_shift($args)), function (?User $user) use ($sender, $nick): void {
			if ($sender instanceof Player && !$sender->isConnected()) return;
			if ($user === null || !$user->valid()) {
				$sender->sendMessage(TextFormat::RI . "No player by this nickname!");
				return;
			}
			$sender->sendMessage(TextFormat::GI . "The player going by this nickname (" . TextFormat::AQUA . $nick . TextFormat::GRAY . ") is " . TextFormat::YELLOW . $user->getGamertag());
		});
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
