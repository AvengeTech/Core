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

class TransferAll extends CoreCommand {

	public $plugin;

	public function __construct(Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args) {
		$network = Core::getInstance()->getNetwork();
		$manager = $network->getServerManager();
		if (count($args) != 1) {
			$sender->sendMessage(TextFormat::RN . "No server provided!");
			return;
		}

		$identifier = strtolower(array_shift($args));
		if (($server = $manager->getServerById($identifier)) === null) {
			$sender->sendMessage(TextFormat::RI . "Invalid server ID provided.");
			return;
		}

		foreach (Server::getInstance()->getOnlinePlayers() as $player) {
			$server->transfer($player, TextFormat::GI . "Connected to " . TextFormat::AQUA . $identifier . TextFormat::GRAY . "!");
		}
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
