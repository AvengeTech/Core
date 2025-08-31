<?php

namespace core\network\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;

use core\{
	Core,
	AtPlayer as Player
};
use core\utils\TextFormat;

class AddUptime extends CoreCommand {

	public function __construct(\core\Core $plugin, $name, $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) === 0) {
			$sender->sendMessage(TextFormat::RI . "Usage: /adduptime <minutes>");
			return;
		}
		$network = Core::getInstance()->getNetwork();
		$network->timeSetup -= ($total = ((int) array_shift($args)) * 60);
		$sender->sendMessage(TextFormat::GI . "Added " . TextFormat::YELLOW . $total . TextFormat::GRAY . " minutes to the uptime!");
	}
}
