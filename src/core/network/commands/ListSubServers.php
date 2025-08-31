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

class ListSubServers extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
		$this->setAliases(["lss"]);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		$self = (bool) (array_shift($args) ?? false);
		$main = (bool) (array_shift($args) ?? false);

		$servers = "";
		foreach (Core::thisServer()->getSubServers($self, $main) as $server) {
			$servers .= $server->getIdentifier() . PHP_EOL;
		}
		$sender->sendMessage($servers);
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
