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
use core\network\server\ServerInstance;
use core\utils\TextFormat;

class Online extends CoreCommand {

	public $plugin;

	public function __construct(Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args) {
		if (count($args) < 1) {
			$sender->sendMessage(TextFormat::RI . "No player provided!");
			return;
		}
		$sm = Core::getInstance()->getNetwork()->getServerManager();
		$pl = array_shift($args);
		$player = Server::getInstance()->getPlayerByPrefix($pl);
		if ($player instanceof Player) {
			if ($player->isStaff() && $player->isVanished() && ($sender instanceof Player && !$sender->isStaff())) {
				$sender->sendMessage(TextFormat::RI . "This player is not online!");
				return;
			}
			$sender->sendMessage(TextFormat::YI . $pl . " is online! (" . TextFormat::AQUA . $sm->getThisServer()->getIdentifier() . TextFormat::GRAY . ")");
			return;
		}

		$server = $sm->getServerByPlayer($pl);
		if (!$server instanceof ServerInstance) {
			$sender->sendMessage(TextFormat::RI . "This player is not online!");
			return;
		}
		$sender->sendMessage(TextFormat::YI . $pl . " is online! (" . TextFormat::AQUA . $server->getIdentifier() . TextFormat::GRAY . ")");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
