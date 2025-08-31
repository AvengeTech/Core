<?php

namespace core\network\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use core\{
	Core,
	AtPlayer as Player
};
use core\command\type\CoreCommand;
use core\rank\Rank;
use core\utils\TextFormat;

class Downtime extends CoreCommand {

	public $plugin;

	public function __construct(Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		// TODO: Command toggle for network downtime
	}

	public function getPlugin(): Core {
		return $this->plugin;
	}
}
